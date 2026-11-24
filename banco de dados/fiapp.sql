CREATE DATABASE IF NOT EXISTS fiapp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE fiapp;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS vencimentos;
DROP TABLE IF EXISTS estoque_receita;
DROP TABLE IF EXISTS ingredientes;
DROP TABLE IF EXISTS estoque;
DROP TABLE IF EXISTS alimentos;
DROP TABLE IF EXISTS receitas;
DROP TABLE IF EXISTS catalogo_alimentos;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id            INT          NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  senha         VARCHAR(255) NOT NULL,
  telefone      VARCHAR(20)  DEFAULT NULL,
  data_cadastro DATE         NOT NULL DEFAULT (CURRENT_DATE),
  ativo         TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB;

CREATE TABLE catalogo_alimentos (
  id   INT          NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_catalogo_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE alimentos (
  id                INT          NOT NULL AUTO_INCREMENT,
  usuario_id        INT          NOT NULL,
  nome              VARCHAR(100) NOT NULL,
  categoria         VARCHAR(50)  NOT NULL,
  unidade_medida    VARCHAR(30)  NOT NULL,
  marca_preferencia VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_alimentos_usuario (usuario_id),
  CONSTRAINT fk_alimentos_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
) ENGINE=InnoDB;

CREATE TABLE receitas (
  id_receita        INT           NOT NULL AUTO_INCREMENT,
  titulo            VARCHAR(100)  NOT NULL,
  modo_preparo      VARCHAR(1000) NOT NULL,
  tempo_preparo     INT           DEFAULT NULL,
  nivel_dificuldade VARCHAR(20)   DEFAULT NULL,
  categoria         VARCHAR(50)   DEFAULT NULL,
  porcoes           INT           DEFAULT NULL,
  PRIMARY KEY (id_receita)
) ENGINE=InnoDB;

CREATE TABLE ingredientes (
  id_ingrediente_receita INT           NOT NULL AUTO_INCREMENT,
  receita_id             INT           NOT NULL,
  alimento_id            INT           NOT NULL,
  quantidade_necessaria  DECIMAL(10,2) NOT NULL,
  unidade_medida         VARCHAR(20)   NOT NULL,
  substitutos_possiveis  VARCHAR(100)  DEFAULT NULL,
  PRIMARY KEY (id_ingrediente_receita),
  KEY idx_ingredientes_receita (receita_id),
  KEY idx_ingredientes_catalogo (alimento_id),
  CONSTRAINT fk_ingredientes_receita
    FOREIGN KEY (receita_id)  REFERENCES receitas (id_receita),
  CONSTRAINT fk_ingredientes_catalogo
    FOREIGN KEY (alimento_id) REFERENCES catalogo_alimentos (id)
) ENGINE=InnoDB;

CREATE TABLE estoque (
  id_item_estoque INT           NOT NULL AUTO_INCREMENT,
  usuario_id      INT           NOT NULL,
  alimento_id     INT           NOT NULL,
  quantidade      DECIMAL(10,2) NOT NULL,
  data_validade   DATE          NOT NULL,
  data_cadastro   DATE          NOT NULL DEFAULT (CURRENT_DATE),
  observacoes     VARCHAR(200)  DEFAULT NULL,
  marca           VARCHAR(100)  DEFAULT NULL,
  PRIMARY KEY (id_item_estoque),
  KEY idx_estoque_usuario (usuario_id),
  KEY idx_estoque_alimento (alimento_id),
  CONSTRAINT fk_estoque_usuario
    FOREIGN KEY (usuario_id)  REFERENCES usuarios (id),
  CONSTRAINT fk_estoque_alimento
    FOREIGN KEY (alimento_id) REFERENCES alimentos (id)
) ENGINE=InnoDB;

CREATE TABLE estoque_receita (
  estoque_id INT NOT NULL,
  receita_id INT NOT NULL,
  PRIMARY KEY (estoque_id, receita_id),
  KEY idx_estoque_receita_receita (receita_id),
  CONSTRAINT fk_estoque_receita_estoque
    FOREIGN KEY (estoque_id) REFERENCES estoque (id_item_estoque),
  CONSTRAINT fk_estoque_receita_receita
    FOREIGN KEY (receita_id) REFERENCES receitas (id_receita)
) ENGINE=InnoDB;

CREATE TABLE vencimentos (
  id_aviso       INT          NOT NULL AUTO_INCREMENT,
  estoque_id     INT          NOT NULL,
  data_alerta    DATETIME     NOT NULL,
  status         ENUM('Pendente','Visualizado','Resolvido') NOT NULL DEFAULT 'Pendente',
  descricao      VARCHAR(200) DEFAULT NULL,
  data_resolucao DATETIME     DEFAULT NULL,
  PRIMARY KEY (id_aviso),
  KEY idx_vencimentos_estoque (estoque_id),
  CONSTRAINT fk_vencimentos_estoque
    FOREIGN KEY (estoque_id) REFERENCES estoque (id_item_estoque)
) ENGINE=InnoDB;

INSERT INTO usuarios (id, nome, email, senha, telefone) VALUES
  (1, 'Maria Silva',   'maria@fiapp.com',    '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000001'),
  (2, 'João Souza',    'joao@fiapp.com',     '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000002'),
  (3, 'Ana Lima',      'ana@fiapp.com',      '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000003'),
  (4, 'Pedro Costa',   'pedro@fiapp.com',    '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000004'),
  (5, 'Carla Dias',    'carla@fiapp.com',    '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000005'),
  (6, 'Lucas Rocha',   'lucas@fiapp.com',    '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000006'),
  (7, 'Fernanda Melo', 'fernanda@fiapp.com', '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000007'),
  (8, 'Rafael Nunes',  'rafael@fiapp.com',   '$2y$10$k58j0nhUTZG19SUO9MZkF./oaBbdXZ8TAoo9rztsZTil8XUz.VPMi', '41990000008');

INSERT INTO catalogo_alimentos (id, nome) VALUES
  (1,  'Leite'),
  (2,  'Queijo'),
  (3,  'Frango'),
  (4,  'Alface'),
  (5,  'Maçã'),
  (6,  'Iogurte'),
  (7,  'Tomate'),
  (8,  'Pão Integral'),
  (9,  'Morango'),
  (10, 'Abacaxi'),
  (11, 'uva passa');

INSERT INTO alimentos (id, usuario_id, nome, categoria, unidade_medida, marca_preferencia) VALUES
  (1, 1, 'Leite',        'Laticínios', 'L',  'Marca A'),
  (2, 1, 'Queijo',       'Laticínios', 'kg', 'Marca B'),
  (3, 2, 'Frango',       'Carnes',     'kg', 'Marca C'),
  (4, 2, 'Alface',       'Vegetais',   'un', 'Horta X'),
  (5, 3, 'Maçã',         'Frutas',     'kg', 'Sítio Y'),
  (6, 3, 'Iogurte',      'Laticínios', 'un', 'Marca D'),
  (7, 4, 'Tomate',       'Vegetais',   'kg', 'Horta X'),
  (8, 6, 'Pão Integral', 'Padaria',    'un', 'Padaria Z');

INSERT INTO receitas (id_receita, titulo, modo_preparo, tempo_preparo, nivel_dificuldade, categoria, porcoes) VALUES
  (1, 'Salada Mista',
      'Lave bem a alface e os tomates. Corte os tomates em pedaços e rasgue as folhas de alface. Coloque tudo em uma tigela, tempere com sal, azeite e limão a gosto. Misture delicadamente e sirva.',
      10, 'Fácil', 'Saladas', 2),
  (2, 'Frango Grelhado',
      'Tempere o frango com sal, alho e outros temperos de sua preferência. Aqueça uma frigideira com um pouco de óleo ou azeite. Coloque o frango e grelhe dos dois lados até ficar bem cozido. Retire do fogo e sirva.',
      25, 'Fácil', 'Carnes', 2),
  (3, 'Vitamina de Maçã',
      'Lave, descasque e corte a maçã em pedaços. Coloque no liquidificador junto com o leite e bata até obter uma mistura cremosa. Se preferir, adicione gelo e bata novamente. Sirva em seguida.',
      5, 'Fácil', 'Bebidas', 1),
  (4, 'Molho de Tomate',
      'Lave os tomates e corte em pedaços pequenos. Coloque em uma panela com um fio de azeite e temperos a gosto. Cozinhe em fogo baixo, mexendo de vez em quando, até os tomates ficarem macios. Amasse ou bata para obter um molho mais uniforme.',
      30, 'Médio', 'Molhos', 4),
  (5, 'Omelete de Queijo',
      'Quebre os ovos em uma tigela e bata com um garfo. Adicione sal e misture. Aqueça uma frigideira untada, despeje os ovos e espalhe o queijo por cima. Cozinhe em fogo baixo até firmar e dobre a omelete ao meio. Sirva ainda quente.',
      10, 'Fácil', 'Café', 1),
  (6, 'Iogurte com Frutas',
      'Coloque o iogurte em uma tigela. Lave e corte as frutas em pedaços pequenos. Acrescente as frutas ao iogurte e misture delicadamente. Se desejar, adicione aveia ou mel para complementar. Sirva gelado.',
      5, 'Fácil', 'Sobremesas', 2),
  (7, 'Sopa de Legumes',
      'Lave e corte os legumes em pedaços pequenos. Coloque-os em uma panela com água e temperos a gosto. Cozinhe em fogo médio até ficarem macios. Se desejar uma sopa cremosa, bata parte dos legumes no liquidificador e misture novamente na panela.',
      35, 'Médio', 'Sopas', 4),
  (8, 'Panqueca de Frango',
      'Cozinhe o frango e desfie em pedaços pequenos. Prepare a massa misturando ovos, leite, farinha e uma pitada de sal até ficar homogênea. Despeje pequenas porções em uma frigideira untada e doure dos dois lados. Recheie com o frango desfiado, enrole e sirva.',
      40, 'Médio', 'Pratos', 3);

INSERT INTO ingredientes (id_ingrediente_receita, receita_id, alimento_id, quantidade_necessaria, unidade_medida, substitutos_possiveis) VALUES
  (1,  1, 4, 1.00, 'un', 'Rúcula'),
  (2,  2, 3, 0.50, 'kg', 'Peito de peru'),
  (3,  3, 5, 0.30, 'kg', 'Banana'),
  (4,  3, 1, 0.20, 'L',  'Leite vegetal'),
  (5,  4, 7, 1.00, 'kg', 'Tomate pelado'),
  (6,  5, 2, 0.10, 'kg', 'Muçarela'),
  (7,  6, 6, 1.00, 'un', 'Iogurte natural'),
  (8,  1, 7, 2.00, 'un', 'Pepino'),
  (9,  6, 5, 0.20, 'kg', 'Banana'),
  (10, 7, 7, 1.00, 'kg', 'Abóbora'),
  (11, 8, 3, 0.30, 'kg', 'Peito de peru');

INSERT INTO estoque (id_item_estoque, usuario_id, alimento_id, quantidade, data_validade, observacoes) VALUES
  (1, 1, 1, 2.00, DATE_ADD(CURDATE(), INTERVAL 7  DAY), 'Carga inicial'),
  (2, 1, 2, 0.50, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Carga inicial'),
  (3, 2, 3, 1.50, DATE_ADD(CURDATE(), INTERVAL 3  DAY), 'Carga inicial'),
  (4, 2, 4, 3.00, DATE_ADD(CURDATE(), INTERVAL 2  DAY), 'Carga inicial'),
  (5, 3, 5, 1.00, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Carga inicial'),
  (6, 3, 6, 6.00, DATE_ADD(CURDATE(), INTERVAL 5  DAY), 'Carga inicial'),
  (7, 4, 7, 2.00, DATE_ADD(CURDATE(), INTERVAL 4  DAY), 'Carga inicial'),
  (8, 6, 8, 4.00, DATE_ADD(CURDATE(), INTERVAL 6  DAY), 'Carga inicial');

INSERT INTO estoque_receita (estoque_id, receita_id) VALUES
  (1, 1),
  (2, 5),
  (3, 2),
  (4, 1),
  (5, 3),
  (6, 6),
  (7, 4),
  (8, 8);

INSERT INTO vencimentos (id_aviso, estoque_id, data_alerta, status, descricao, data_resolucao) VALUES
  (1, 4, NOW(), 'Pendente',    'Alface vence em 2 dias',  NULL),
  (2, 3, NOW(), 'Pendente',    'Frango vence em 3 dias',  NULL),
  (3, 7, NOW(), 'Visualizado', 'Tomate vence em 4 dias',  NULL),
  (4, 6, NOW(), 'Pendente',    'Iogurte vence em 5 dias', NULL),
  (5, 1, NOW(), 'Resolvido',   'Leite consumido',         NOW()),
  (6, 5, NOW(), 'Pendente',    'Maçã vence em 10 dias',   NULL),
  (7, 2, NOW(), 'Pendente',    'Queijo vence em 15 dias', NULL),
  (8, 8, NOW(), 'Visualizado', 'Pão vence em 6 dias',     NULL);
