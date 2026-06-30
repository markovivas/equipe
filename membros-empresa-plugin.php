<?php
/**
 * Plugin Name: Equipe de Trabalho
 * Description: Plugin para exibir os membros da equipe com nome, secretaria, setor, profissão, função, foto e contato.
 * Version: 2.0
 * Author: Marco Antônio Vivas
 * Text Domain: membros-empresa
 */

register_activation_hook(__FILE__, function () {
    add_option('membros_empresa_data', []);
});

add_action('admin_menu', 'membros_admin_menu');
function membros_admin_menu() {
    add_menu_page(
        'Gerenciar Equipe',
        'Equipe',
        'manage_options',
        'membros-empresa',
        'membros_admin_page',
        'dashicons-groups',
        30
    );
}

add_action('admin_enqueue_scripts', 'membros_admin_scripts');
function membros_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_membros-empresa') {
        return;
    }
    wp_enqueue_media();
}

function membros_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Permissão negada.');
    }

    $option = 'membros_empresa_data';
    $membros = get_option($option, []);
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';
    $nonce_verified = isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'membros_action');

    if ($action === 'delete' && $nonce_verified && isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        if (isset($membros[$id])) {
            array_splice($membros, $id, 1);
            update_option($option, array_values($membros));
            echo '<div class="notice notice-success"><p>Membro removido com sucesso.</p></div>';
        }
        $action = 'list';
    }

    if ($action === 'save' && $nonce_verified) {
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : -1;
        $nome = sanitize_text_field($_POST['nome'] ?? '');
        $secretaria = sanitize_text_field($_POST['secretaria'] ?? '');
        $setor = sanitize_text_field($_POST['setor'] ?? '');
        $profissao = sanitize_text_field($_POST['profissao'] ?? '');
        $funcao = sanitize_text_field($_POST['funcao'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $telefone = sanitize_text_field($_POST['telefone'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $localizacao = sanitize_text_field($_POST['localizacao'] ?? '');
        $pais = sanitize_text_field($_POST['pais'] ?? '');
        $foto = sanitize_text_field($_POST['foto'] ?? '');

        if (empty($nome)) {
            echo '<div class="notice notice-error"><p>O campo Nome é obrigatório.</p></div>';
        } else {
            $membro = [
                'nome' => $nome,
                'secretaria' => $secretaria,
                'setor' => $setor,
                'profissao' => $profissao,
                'funcao' => $funcao,
                'foto' => $foto,
                'contato' => ['email' => $email, 'telefone' => $telefone],
                'status' => $status,
                'localizacao' => $localizacao,
                'pais' => $pais,
            ];

            if ($id >= 0 && isset($membros[$id])) {
                $membros[$id] = $membro;
                $msg = 'Membro atualizado com sucesso.';
            } else {
                $membros[] = $membro;
                $msg = 'Membro adicionado com sucesso.';
            }

            update_option($option, $membros);
            echo '<div class="notice notice-success"><p>' . $msg . '</p></div>';
            $action = 'list';
        }
    }

    if ($action === 'edit' && isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $membro = $membros[$id] ?? null;
        if ($membro) {
            membros_exibir_formulario($membro, $id);
            return;
        }
        echo '<div class="notice notice-error"><p>Membro não encontrado.</p></div>';
    }

    if ($action === 'add') {
        membros_exibir_formulario(null, -1);
        return;
    }

    membros_exibir_lista($membros);
}

function membros_exibir_lista($membros) {
    ?>
    <div class="wrap">
        <h1>Gerenciar Equipe</h1>
        <a href="?page=membros-empresa&action=add" class="page-title-action">Adicionar Novo</a>
        <hr class="wp-header-end">
        <?php if (empty($membros)): ?>
            <p>Nenhum membro cadastrado. <a href="?page=membros-empresa&action=add">Adicione o primeiro</a>.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped" style="margin-top:15px;">
                <thead>
                    <tr>
                        <th style="width:60px;">Foto</th>
                        <th>Nome</th>
                        <th>Secretaria</th>
                        <th>Setor</th>
                        <th>Função</th>
                        <th>Contato</th>
                        <th style="width:120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($membros as $i => $m): ?>
                        <tr>
                            <td>
                                <?php if (!empty($m['foto'])): ?>
                                    <img src="<?php echo esc_url($m['foto']); ?>" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html($m['nome']); ?></strong></td>
                            <td><?php echo esc_html($m['secretaria']); ?></td>
                            <td><?php echo esc_html($m['setor']); ?></td>
                            <td><?php echo esc_html($m['funcao']); ?></td>
                            <td>
                                <?php if (!empty($m['contato']['email'])): ?>
                                    <a href="mailto:<?php echo esc_attr($m['contato']['email']); ?>"><?php echo esc_html($m['contato']['email']); ?></a><br>
                                <?php endif; ?>
                                <?php echo esc_html($m['contato']['telefone'] ?? ''); ?>
                            </td>
                            <td>
                                <a href="?page=membros-empresa&action=edit&id=<?php echo $i; ?>" class="button button-small">Editar</a>
                                <a href="<?php echo wp_nonce_url('?page=membros-empresa&action=delete&id=' . $i, 'membros_action'); ?>" class="button button-small" onclick="return confirm('Tem certeza que deseja excluir este membro?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

function membros_exibir_formulario($membro, $id) {
    $is_edit = $id >= 0;
    $nome = $membro['nome'] ?? '';
    $secretaria = $membro['secretaria'] ?? '';
    $setor = $membro['setor'] ?? '';
    $profissao = $membro['profissao'] ?? '';
    $funcao = $membro['funcao'] ?? '';
    $email = $membro['contato']['email'] ?? '';
    $telefone = $membro['contato']['telefone'] ?? '';
    $status = $membro['status'] ?? '';
    $localizacao = $membro['localizacao'] ?? '';
    $pais = $membro['pais'] ?? '';
    $foto = $membro['foto'] ?? '';
    ?>
    <div class="wrap">
        <h1><?php echo $is_edit ? 'Editar Membro' : 'Adicionar Novo Membro'; ?></h1>
        <a href="?page=membros-empresa" class="page-title-action">&larr; Voltar para lista</a>
        <hr class="wp-header-end">
        <form method="post" action="?page=membros-empresa&action=save<?php echo $is_edit ? '&id=' . $id : ''; ?>" style="max-width:650px;margin-top:20px;">
            <?php wp_nonce_field('membros_action'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome *</label></th>
                    <td><input type="text" id="nome" name="nome" value="<?php echo esc_attr($nome); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="secretaria">Secretaria</label></th>
                    <td><input type="text" id="secretaria" name="secretaria" value="<?php echo esc_attr($secretaria); ?>" class="regular-text" placeholder="Ex: SECOM"></td>
                </tr>
                <tr>
                    <th><label for="setor">Setor</label></th>
                    <td><input type="text" id="setor" name="setor" value="<?php echo esc_attr($setor); ?>" class="regular-text" placeholder="Ex: Comunicação"></td>
                </tr>
                <tr>
                    <th><label for="profissao">Profissão</label></th>
                    <td><input type="text" id="profissao" name="profissao" value="<?php echo esc_attr($profissao); ?>" class="regular-text" placeholder="Ex: Jornalista"></td>
                </tr>
                <tr>
                    <th><label for="funcao">Função</label></th>
                    <td><input type="text" id="funcao" name="funcao" value="<?php echo esc_attr($funcao); ?>" class="regular-text" placeholder="Ex: Chefe de Jornalista"></td>
                </tr>
                <tr>
                    <th><label>Foto</label></th>
                    <td>
                        <div class="foto-preview" style="margin-bottom:10px;">
                            <?php if ($foto): ?>
                                <img src="<?php echo esc_url($foto); ?>" style="max-width:150px;max-height:150px;border-radius:8px;display:block;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="foto" name="foto" value="<?php echo esc_attr($foto); ?>">
                        <button type="button" class="button" id="escolher-foto">Selecionar Imagem</button>
                        <button type="button" class="button" id="remover-foto">Remover Imagem</button>
                    </td>
                </tr>
                <tr>
                    <th><label for="email">E-mail</label></th>
                    <td><input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>" class="regular-text" placeholder="Ex: nome@dominio.com.br"></td>
                </tr>
                <tr>
                    <th><label for="telefone">Telefone</label></th>
                    <td><input type="text" id="telefone" name="telefone" value="<?php echo esc_attr($telefone); ?>" class="regular-text" placeholder="Ex: (35) 91122-3344"></td>
                </tr>
                <tr>
                    <th><label for="status">Status</label></th>
                    <td>
                        <select id="status" name="status">
                            <option value="" <?php selected($status, ''); ?>>Nenhum</option>
                            <option value="online" <?php selected($status, 'online'); ?>>Online</option>
                            <option value="offline" <?php selected($status, 'offline'); ?>>Offline</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="localizacao">Localização</label></th>
                    <td><input type="text" id="localizacao" name="localizacao" value="<?php echo esc_attr($localizacao); ?>" class="regular-text" placeholder="Ex: Centro Administrativo"></td>
                </tr>
                <tr>
                    <th><label for="pais">País</label></th>
                    <td><input type="text" id="pais" name="pais" value="<?php echo esc_attr($pais); ?>" class="regular-text" placeholder="Ex: Brasil"></td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php echo $is_edit ? 'Atualizar Membro' : 'Adicionar Membro'; ?></button>
            </p>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var frame;
        $('#escolher-foto').on('click', function(e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Selecionar Foto',
                button: { text: 'Usar esta foto' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#foto').val(attachment.url);
                $('.foto-preview').html('<img src="' + attachment.url + '" style="max-width:150px;max-height:150px;border-radius:8px;display:block;">');
            });
            frame.open();
        });
        $('#remover-foto').on('click', function() {
            $('#foto').val('');
            $('.foto-preview').html('');
        });
    });
    </script>
    <?php
}

function exibir_membros_empresa($atts) {
    $membros = get_option('membros_empresa_data', []);

    if (empty($membros)) {
        return '<p>Nenhum membro cadastrado.</p>';
    }

    $membros_por_pagina = 4;
    $pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $total_membros = count($membros);
    $total_paginas = ceil($total_membros / $membros_por_pagina);
    $indice_inicial = ($pagina_atual - 1) * $membros_por_pagina;

    $output = '<div class="membros-container">';

    $output .= '<div class="filtros">';
    $output .= '<div class="busca-container">';
    $output .= '<input type="text" id="busca-nome" placeholder="Buscar por nome..." class="campo-busca">';
    $output .= '<button onclick="filtrarMembros()" class="botao-busca">Buscar</button>';
    $output .= '</div>';

    $output .= '<select name="secretaria" id="filtro-secretaria"><option value="">Secretaria...</option>';
    $secretarias = array_unique(array_column($membros, 'secretaria'));
    foreach ($secretarias as $sec) {
        $output .= '<option value="' . esc_attr($sec) . '">' . esc_html($sec) . '</option>';
    }
    $output .= '</select>';

    $output .= '<select name="setor" id="filtro-setor"><option value="">Setor...</option>';
    $setores = array_unique(array_column($membros, 'setor'));
    foreach ($setores as $set) {
        $output .= '<option value="' . esc_attr($set) . '">' . esc_html($set) . '</option>';
    }
    $output .= '</select>';

    $output .= '<select name="funcao" id="filtro-funcao"><option value="">Função...</option>';
    $funcoes = array_unique(array_column($membros, 'profissao'));
    foreach ($funcoes as $fun) {
        $output .= '<option value="' . esc_attr($fun) . '">' . esc_html($fun) . '</option>';
    }
    $output .= '</select>';

    $output .= '<button class="limpar-filtros" onclick="limparFiltros()">Limpar filtros</button>';
    $output .= '</div>';

    $output .= '<div class="alfabeto">';
    foreach (range('A', 'Z') as $letra) {
        $output .= '<button onclick="filtrarPorLetra(\'' . $letra . '\')">' . $letra . '</button>';
    }
    $output .= '</div>';

    $output .= '<div class="membros-empresa">';
    foreach ($membros as $index => $membro) {
        $foto_url = !empty($membro['foto']) ? $membro['foto'] : '';
        $display_style = ($index >= $indice_inicial && $index < $indice_inicial + $membros_por_pagina) ? '' : 'display: none;';

        $output .= '<div class="membro-card" data-nome="' . esc_attr($membro['nome']) . '"
                    data-secretaria="' . esc_attr($membro['secretaria']) . '"
                    data-setor="' . esc_attr($membro['setor']) . '"
                    data-funcao="' . esc_attr($membro['profissao']) . '"
                    style="' . $display_style . '">';
        $output .= '<div class="membro-foto-container">';
        if ($foto_url) {
            $output .= '<img src="' . esc_url($foto_url) . '" alt="' . esc_attr($membro['nome']) . '" class="membro-foto">';
        } else {
            $output .= '<div class="membro-foto-placeholder">' . esc_html(substr($membro['nome'], 0, 2)) . '</div>';
        }
        $output .= '</div>';
        $output .= '<div class="membro-info">';
        $output .= '<h3>' . esc_html($membro['nome']) . ' <span class="status ' . esc_attr($membro['status']) . '"></span></h3>';
        $output .= '<p><strong>Secretaria:</strong> ' . esc_html($membro['secretaria']) . '</p>';
        $output .= '<p><strong>Setor:</strong> ' . esc_html($membro['setor']) . '</p>';
        $output .= '<p><strong>Função:</strong> ' . esc_html($membro['funcao']) . '</p>';
        $output .= '<div class="contato-icons">';
        $output .= '<a href="mailto:' . esc_attr($membro['contato']['email']) . '" title="Enviar e-mail"><span class="dashicons dashicons-email"></span></a>';
        $output .= '<a href="tel:' . esc_attr($membro['contato']['telefone']) . '" title="Ligar"><span class="dashicons dashicons-phone"></span></a>';
        $output .= '</div>';
        $output .= '</div></div>';
    }
    $output .= '</div>';

    if ($total_paginas > 1) {
        $output .= '<div class="paginacao">';
        if ($pagina_atual > 1) {
            $output .= '<a href="' . add_query_arg('pagina', 1) . '" class="pagina-link">&laquo; Primeira</a>';
        }
        for ($i = max(1, $pagina_atual - 2); $i < $pagina_atual; $i++) {
            $output .= '<a href="' . add_query_arg('pagina', $i) . '" class="pagina-link">' . $i . '</a>';
        }
        $output .= '<span class="pagina-atual">' . $pagina_atual . '</span>';
        for ($i = $pagina_atual + 1; $i <= min($pagina_atual + 2, $total_paginas); $i++) {
            $output .= '<a href="' . add_query_arg('pagina', $i) . '" class="pagina-link">' . $i . '</a>';
        }
        if ($pagina_atual < $total_paginas) {
            $output .= '<a href="' . add_query_arg('pagina', $total_paginas) . '" class="pagina-link">Última &raquo;</a>';
        }
        $output .= '</div>';
    }

    $output .= '<style>
        .membros-container {
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .membros-empresa {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        .membro-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .membro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .membro-foto-container {
            background-color: #f5f7fa;
            padding: 20px;
            text-align: center;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .membro-foto {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .membro-foto-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #3498db;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .membro-info {
            padding: 20px;
        }

        .membro-card h3 {
            font-size: 18px;
            margin: 0 0 10px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .membro-card p {
            font-size: 14px;
            color: #7f8c8d;
            margin: 8px 0;
            line-height: 1.4;
        }

        .membro-card .status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .membro-card .status.offline {
            background-color: #e74c3c;
        }

        .membro-card .status.online {
            background-color: #2ecc71;
        }

        .membro-card .contato-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ecf0f1;
        }

        .membro-card .contato-icons a {
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .membro-card .contato-icons a:hover {
            color: #2980b9;
        }

        .filtros {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .busca-container {
            display: flex;
            gap: 10px;
            flex: 1;
            min-width: 250px;
        }

        .campo-busca {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            flex: 1;
            min-width: 150px;
        }

        .botao-busca {
            padding: 8px 16px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .botao-busca:hover {
            background-color: #2980b9;
        }

        .filtros select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            min-width: 150px;
            background-color: #f8f9fa;
        }

        .limpar-filtros {
            padding: 8px 16px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .limpar-filtros:hover {
            background-color: #c0392b;
        }

        .alfabeto {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .alfabeto button {
            width: 36px;
            height: 36px;
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #3498db;
            transition: all 0.3s ease;
        }

        .alfabeto button:hover {
            background-color: #3498db;
            color: #fff;
            border-color: #3498db;
        }

        .paginacao {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagina-link, .pagina-atual {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }

        .pagina-link {
            background-color: #f8f9fa;
            color: #3498db;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .pagina-link:hover {
            background-color: #3498db;
            color: #fff;
            border-color: #3498db;
        }

        .pagina-atual {
            background-color: #3498db;
            color: #fff;
            border: 1px solid #3498db;
        }

        @media (max-width: 768px) {
            .filtros {
                flex-direction: column;
                align-items: stretch;
            }

            .filtros select, .campo-busca {
                width: 100%;
            }

            .busca-container {
                flex-direction: column;
            }

            .membros-empresa {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>';

    $output .= '<script>
        const todosMembros = ' . json_encode($membros) . ';
        const membrosPorPagina = ' . $membros_por_pagina . ';
        let paginaAtual = ' . $pagina_atual . ';
        let membrosFiltrados = [];

        function filtrarMembros() {
            const termo = document.getElementById("busca-nome").value.toLowerCase();
            const secretaria = document.getElementById("filtro-secretaria").value;
            const setor = document.getElementById("filtro-setor").value;
            const funcao = document.getElementById("filtro-funcao").value;

            membrosFiltrados = todosMembros.filter(membro => {
                const nomeMatch = membro.nome.toLowerCase().includes(termo);
                const secretariaMatch = secretaria === "" || membro.secretaria === secretaria;
                const setorMatch = setor === "" || membro.setor === setor;
                const funcaoMatch = funcao === "" || membro.profissao === funcao;
                return nomeMatch && secretariaMatch && setorMatch && funcaoMatch;
            });

            atualizarExibicaoMembros();
            atualizarPaginacao();
        }

        function filtrarPorLetra(letra) {
            membrosFiltrados = todosMembros.filter(membro =>
                membro.nome.toUpperCase().startsWith(letra)
            );
            document.getElementById("busca-nome").value = "";
            document.getElementById("filtro-secretaria").value = "";
            document.getElementById("filtro-setor").value = "";
            document.getElementById("filtro-funcao").value = "";
            atualizarExibicaoMembros();
            atualizarPaginacao();
        }

        function limparFiltros() {
            document.getElementById("busca-nome").value = "";
            document.getElementById("filtro-secretaria").value = "";
            document.getElementById("filtro-setor").value = "";
            document.getElementById("filtro-funcao").value = "";
            membrosFiltrados = [...todosMembros];
            paginaAtual = 1;
            atualizarExibicaoMembros();
            atualizarPaginacao();
        }

        function atualizarExibicaoMembros() {
            const cards = document.querySelectorAll(".membro-card");
            const totalMembros = membrosFiltrados.length > 0 ? membrosFiltrados.length : todosMembros.length;
            const membrosAtivos = membrosFiltrados.length > 0 ? membrosFiltrados : todosMembros;
            cards.forEach(card => card.style.display = "none");
            const inicio = (paginaAtual - 1) * membrosPorPagina;
            const fim = inicio + membrosPorPagina;
            for (let i = inicio; i < fim && i < membrosAtivos.length; i++) {
                const membro = membrosAtivos[i];
                const card = Array.from(cards).find(c =>
                    c.getAttribute("data-nome") === membro.nome
                );
                if (card) {
                    card.style.display = "block";
                }
            }
        }

        function atualizarPaginacao() {
            const totalMembros = membrosFiltrados.length > 0 ? membrosFiltrados.length : todosMembros.length;
            const totalPaginas = Math.ceil(totalMembros / membrosPorPagina);
        }

        document.getElementById("busca-nome").addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                filtrarMembros();
            }
        });

        document.getElementById("filtro-secretaria").addEventListener("change", filtrarMembros);
        document.getElementById("filtro-setor").addEventListener("change", filtrarMembros);
        document.getElementById("filtro-funcao").addEventListener("change", filtrarMembros);

        document.addEventListener("DOMContentLoaded", function() {
            membrosFiltrados = [...todosMembros];
        });
    </script>';

    $output .= '</div>';
    return $output;
}
add_shortcode('membros_empresa', 'exibir_membros_empresa');
