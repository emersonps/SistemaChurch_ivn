# Changelog

## v2026.04.01 - 2026-04-01

### Added
- Adicionada a ficha completa do membro, com visualização centralizada e atalhos para edição, histórico e carteirinha.
- Implementada importação de membros por planilha CSV com seleção de congregação e modelo de arquivo.
- Criada a categoria de eventos internos na listagem administrativa.
- Adicionado o tipo Centro de Custo em contas e caixas.
- Criado o gerenciamento de Naturezas Contábeis com cadastro, edição, exclusão e vínculo às contas contábeis.
- Criada a opção de entradas não contabilizadas, sem impacto em saldos, relatórios e fechamentos.
- Criada a opção de saídas não contabilizadas, sem impacto em saldos, relatórios e fechamentos.
- Adicionadas migrações para permissões faltantes.
- Adicionada migração para incluir Centro de Custo no tipo de conta financeira.
- Adicionada migração para criação de naturezas contábeis.
- Adicionadas migrações para entradas e saídas não contabilizadas.

### Changed
- Simplificada a navegação da lista e da edição de membros, removendo ações redundantes.
- Ajustada a lista de permissões com novos slugs de administração, grupos/células e financeiro.
- Ajustados dashboard, relatórios financeiros e fechamento para considerar apenas lançamentos contabilizados.

### Fixed
- Ocultada a permissão de acesso de desenvolvedor para administradores não desenvolvedores.
- Corrigida a visibilidade de eventos internos para respeitar seleção exclusiva entre membros e grupos/congregações.
- Implementado bloqueio de alteração e exclusão de entradas e saídas em períodos com fechamento financeiro.
