# Manual do Usuário - Sistema de Gestão de Igreja

## Visão Geral
Este manual descreve as principais funcionalidades do sistema de gestão, com foco nas atualizações recentes de Grupos/Células e Finanças.

---

## 1. Gestão de Membros

### Cadastro e Edição
Ao cadastrar ou editar um membro, você pode definir diversos detalhes pessoais e eclesiásticos.
- **Status**: O status "Congregando" é fundamental. Apenas membros com este status podem assumir liderança de células.
- **Automação de Saída**: Se você alterar o status de um membro para qualquer opção que não seja "Congregando" (ex: "Desligado", "Pediu Carta"), o sistema verificará automaticamente se ele é Líder ou Anfitrião de alguma célula. Se for, ele será **removido** dessas funções automaticamente e você receberá um alerta para designar novos responsáveis.

---

## 2. Grupos e Células

### Criação e Edição de Grupos
- **Líder e Anfitrião**: Ao selecionar um Líder ou Anfitrião, o sistema exibe apenas membros elegíveis (que estão "Congregando" e não lideram outros grupos).
- **Sincronização de Funções**: Ao definir o Líder e o Anfitrião nas configurações do grupo, eles são automaticamente adicionados à lista de participantes com as funções corretas ("Líder" e "Anfitrião"). Se você trocar o líder, o antigo voltará a ser um membro comum automaticamente.

### Gestão de Participantes
Na tela de detalhes do grupo, você pode gerenciar os participantes:

#### Adicionar Participante
- **Busca Inteligente**: No campo "Nome do Participante", você pode digitar o nome.
    - Se o nome já existir no banco de dados, selecione-o na lista.
    - Se for um nome novo, basta digitar e o sistema criará automaticamente um cadastro de "Convidado" para essa pessoa.
- **Funções**:
    - **Membro**: Participante regular da célula.
    - **Auxiliar**: Auxilia o líder.
    - **Convidado**: Visitante ou pessoa não batizada que frequenta o grupo. (Se você digitar um nome novo, esta opção é selecionada automaticamente).

#### Conversão de Convidados
Participantes marcados como **Convidado** exibem um botão verde (Check) na lista.
Ao clicar neste botão, você pode registrar a mudança de vida do convidado:
- **Aceitou Jesus**: Transforma o convidado em Membro (Novo Convertido) e registra a data.
- **Reconciliou-se**: Transforma em Membro e registra a data de reconciliação.
- **Tornou-se Membro**: Apenas altera o papel para Membro.

#### Transferência
Você pode transferir membros de um grupo para outro facilmente usando o botão de transferência (ícone de setas).

---

## 3. Financeiro (Saídas / Despesas)

### Listagem e Filtros
A tela de Saídas/Despesas permite visualizar todos os gastos lançados.
- **Filtros**: Você pode filtrar por Data de Início e Data de Fim.
- **Limpar Filtros**: Um botão "Limpar" permite resetar rapidamente a busca para ver todos os registros.

### Paginação
- **Controles**: No rodapé da tabela, você encontra controles completos de paginação.
- **Itens por Página**: Você pode escolher visualizar 10, 25, 50 ou 100 registros por vez.
- **Contador**: O sistema informa "Mostrando X a Y de Z registros" para facilitar a navegação.

---

## 4. Dízimos e Ofertas
- Lançamento rápido de entradas.
- Geração automática de recibos.
- Opção de envio de comprovante via WhatsApp.
