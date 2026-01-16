<?php
/**
 * Plugin Name: Equipe de Trabalho
 * Description: Plugin para exibir os membros da equipe com nome, secretaria, setor, profissão, função, foto e contato.
 * Version: 1.1
 * Author: Marco Antônio Vivas
 * Text Domain: membros-empresa
 */

// Função para registrar o shortcode
function exibir_membros_empresa($atts) {
    // Dados estáticos dos membros (pode ser substituído por um banco de dados)
   
    // Carrega os dados do arquivo separado
    $membros = include plugin_dir_path(__FILE__) . 'membros-data.php';
	
    // Configurações de paginação
    $membros_por_pagina = 4;
    $pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $total_membros = count($membros);
    $total_paginas = ceil($total_membros / $membros_por_pagina);
    $indice_inicial = ($pagina_atual - 1) * $membros_por_pagina;
    $membros_paginados = array_slice($membros, $indice_inicial, $membros_por_pagina);

    // HTML de saída
    $output = '<div class="membros-container">';
    
    // Filtros e busca
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

    // Alfabeto
    $output .= '<div class="alfabeto">';
    foreach (range('A', 'Z') as $letra) {
        $output .= '<button onclick="filtrarPorLetra(\'' . $letra . '\')">' . $letra . '</button>';
    }
    $output .= '</div>';

    // Lista de membros (todos, mas inicialmente apenas os da página atual visíveis)
    $output .= '<div class="membros-empresa">';
    foreach ($membros as $index => $membro) {
        $foto_url = plugins_url('images/' . $membro['foto'], __FILE__);
        $display_style = ($index >= $indice_inicial && $index < $indice_inicial + $membros_por_pagina) ? '' : 'display: none;';
        
        $output .= '<div class="membro-card" data-nome="' . esc_attr($membro['nome']) . '" 
                    data-secretaria="' . esc_attr($membro['secretaria']) . '" 
                    data-setor="' . esc_attr($membro['setor']) . '" 
                    data-funcao="' . esc_attr($membro['profissao']) . '"
                    style="' . $display_style . '">';
        $output .= '<div class="membro-foto-container"><img src="' . esc_url($foto_url) . '" alt="' . esc_attr($membro['nome']) . '" class="membro-foto"></div>';
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

    // Paginação
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

    // Estilos CSS (mantenha os mesmos)
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

    // JavaScript modificado
    $output .= '<script>
        // Armazena todos os membros em uma variável JavaScript
        const todosMembros = ' . json_encode($membros) . ';
        const membrosPorPagina = ' . $membros_por_pagina . ';
        let paginaAtual = ' . $pagina_atual . ';
        let membrosFiltrados = [];
        
        function filtrarMembros() {
            const termo = document.getElementById("busca-nome").value.toLowerCase();
            const secretaria = document.getElementById("filtro-secretaria").value;
            const setor = document.getElementById("filtro-setor").value;
            const funcao = document.getElementById("filtro-funcao").value;
            
            // Filtra todos os membros
            membrosFiltrados = todosMembros.filter(membro => {
                const nomeMatch = membro.nome.toLowerCase().includes(termo);
                const secretariaMatch = secretaria === "" || membro.secretaria === secretaria;
                const setorMatch = setor === "" || membro.setor === setor;
                const funcaoMatch = funcao === "" || membro.profissao === funcao;
                
                return nomeMatch && secretariaMatch && setorMatch && funcaoMatch;
            });
            
            // Atualiza a exibição
            atualizarExibicaoMembros();
            atualizarPaginacao();
        }
        
        function filtrarPorLetra(letra) {
            membrosFiltrados = todosMembros.filter(membro => 
                membro.nome.toUpperCase().startsWith(letra)
            );
            
            // Resetar outros filtros
            document.getElementById("busca-nome").value = "";
            document.getElementById("filtro-secretaria").value = "";
            document.getElementById("filtro-setor").value = "";
            document.getElementById("filtro-funcao").value = "";
            
            // Atualizar exibição
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
            const totalPaginas = Math.ceil(totalMembros / membrosPorPagina);
            
            // Se não houver filtro, use todos os membros
            const membrosAtivos = membrosFiltrados.length > 0 ? membrosFiltrados : todosMembros;
            
            // Oculta todos os cards primeiro
            cards.forEach(card => card.style.display = "none");
            
            // Mostra apenas os membros da página atual que correspondem aos filtros
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
            
            // Aqui você pode atualizar a paginação dinamicamente se quiser
            // Ou simplesmente recarregar a página com os parâmetros de filtro
        }
        
        // Event listeners
        document.getElementById("busca-nome").addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                filtrarMembros();
            }
        });
        
        document.getElementById("filtro-secretaria").addEventListener("change", filtrarMembros);
        document.getElementById("filtro-setor").addEventListener("change", filtrarMembros);
        document.getElementById("filtro-funcao").addEventListener("change", filtrarMembros);
        
        // Inicializa
        document.addEventListener("DOMContentLoaded", function() {
            membrosFiltrados = [...todosMembros];
        });
    </script>';

    $output .= '</div>';
    return $output;
}
add_shortcode('membros_empresa', 'exibir_membros_empresa');
?>