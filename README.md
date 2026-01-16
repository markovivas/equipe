# Membros da Empresa

Plugin WordPress para exibir os membros da empresa com informações detalhadas, incluindo nome, secretaria, setor, profissão, função, foto e contato.

## Descrição

Este plugin permite listar e filtrar membros da empresa em uma página ou post do WordPress, facilitando a visualização e busca por informações relevantes de cada colaborador.

- Exibe nome, secretaria, setor, profissão, função, foto e contato (e-mail e telefone).
- Permite busca por nome e filtros por secretaria, setor e função.
- Paginação automática para grandes listas.
- Interface moderna e responsiva.
- Shortcode fácil de usar.

## Instalação

1. Faça upload da pasta do plugin para o diretório `wp-content/plugins/` do seu WordPress.
2. Ative o plugin no painel do WordPress em **Plugins > Plugins instalados**.
3. Adicione o shortcode `[membros_empresa]` na página ou post onde deseja exibir a lista de membros.

## Uso

Basta inserir o shortcode abaixo em qualquer página ou post:

```
[membros_empresa]
```

## Funcionalidades

- Busca por nome.
- Filtros por secretaria, setor e função.
- Paginação automática.
- Exibição de foto, nome, profissão, função, secretaria, setor, localização, status, país e contatos.
- Interface amigável e personalizável via CSS.

## Estrutura dos Dados

Os dados dos membros estão definidos diretamente no código PHP do plugin, podendo ser facilmente adaptados para integração com banco de dados futuramente.

## Personalização

- As fotos dos membros devem ser adicionadas na pasta `images/` do plugin.
- Para adicionar ou editar membros, altere o array `$membros` no arquivo `membros-empresa-plugin.php`.

## Exemplo de Membro

```php
[
    'nome' => 'Nome do Membro',
    'secretaria' => 'Secretaria',
    'setor' => 'Setor',
    'profissao' => 'Profissão',
    'funcao' => 'Função',
    'foto' => 'nome-da-foto.jpg',
    'contato' => [
        'email' => 'email@exemplo.com',
        'telefone' => '000-000-0000'
    ],
    'status' => 'online',
    'localizacao' => 'Localização',
    'pais' => 'Brasil'
]
```

## Autor

Marco Antonio Vivas