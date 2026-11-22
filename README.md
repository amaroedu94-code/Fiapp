FIAPP — Organizador Inteligente de Alimentos

O FIAPP é um sistema web criado para ajudar os usuários a reduzir o desperdício de alimentos em casa. O usuário cadastra os alimentos que possui, com quantidades e datas de validade, e o sistema acompanha os vencimentos, emite avisos e sugere receitas que podem ser feitas com o que já está disponível, priorizando os itens mais próximos de vencer.



🚀 Funcionalidades Principais

* **Cadastro de Alimentos e Estoque:** Controle de itens por categoria, unidade de medida, marca e data de validade.
* **Controle de Vencimentos:** Painel visual separando itens vencidos, que vencem hoje, próximos do vencimento (7 dias) e dentro do prazo.
* **Sugestão Inteligente de Receitas:** Cruzamento automático com o estoque (incluindo conversão de medidas e sugestão de ingredientes substitutos), priorizando receitas que aproveitam alimentos prestes a vencer.



🛠️ Tecnologias Utilizadas

* **Front-end:** HTML5, CSS3, JavaScript
* **Back-end:** PHP
* **Banco de Dados:** MySQL / MariaDB



⚙️ Como rodar o projeto localmente

1. **Baixe o projeto** e coloque a pasta `fiapp` dentro do diretório `htdocs` do seu **XAMPP** (ex: `C:\xampp\htdocs\fiapp`).
2. **Inicie os serviços:** Abra o painel do XAMPP e inicie o **Apache** e o **MySQL**.
3. **Importe o banco de dados:**
   * Acesse `http://localhost/phpmyadmin` (ou abra o MySQL Workbench).
   * Importe o arquivo `.sql` localizado na pasta `banco de dados` deste repositório (ele criará o banco `fiapp` com as tabelas e dados de exemplo).
4. **Verifique a porta de conexão:**
   * No arquivo `conexao.php`, a porta configurada é a `3307`. Caso o MySQL do seu computador utilize a porta padrão, altere `$porta = 3306;` para `$porta = 3307;`.
5. **Acesse no navegador:**
   * Abra `http://localhost/fiapp/login.php` e crie uma conta ou utilize um usuário cadastrado no banco.
