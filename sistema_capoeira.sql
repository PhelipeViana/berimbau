-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/05/2026 às 15:55
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_capoeira`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT 'padrao.png',
  `nome` varchar(255) NOT NULL,
  `apelido` varchar(100) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `celular` varchar(25) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `mae` varchar(255) DEFAULT NULL,
  `pai` varchar(255) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `graduacao` varchar(100) DEFAULT NULL,
  `docente` varchar(100) DEFAULT NULL,
  `local_treino` varchar(150) DEFAULT NULL,
  `saude` text DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `foto`, `nome`, `apelido`, `nascimento`, `celular`, `email`, `senha`, `status`, `mae`, `pai`, `endereco`, `cidade`, `graduacao`, `docente`, `local_treino`, `saude`, `data_cadastro`, `usuario_id`) VALUES
(1, '69d018fba4ea6.jpg', 'DIEGO CÉSAR DE ARRUDA BORGES', 'PINÓQUIO', '1982-01-02', '065992616665', 'suporte.dct@gmail.com', NULL, 'pendente', 'GRÁCIE EMÍLIE ZÁTTAR', '', 'RUA BICUDO, 02 - CPA 4, TERCEIRA ETAPA', '', 'VERDE (GRADUADO)', 'MESTRE BIRO', '', 'HIPERTENSO, CARDÍACO (DOIS STENTES NO CORAÇÃO)', '2026-04-03 12:58:40', 1),
(6, '69d6b38035ed7.jpg', 'KARLA DO CARMO GARCIA DUARTE MECENA', '', '1981-07-16', '65992645565', 'karlamecena@gmail.com', '$2y$10$COthabEg4taA/rU4c6pPeOv8SW0f0vT01S7cYQuYvX8cBc1Yl8nzm', 'ativo', '', '', '', 'Cuiabá', 'INICIANTE', '', '', 'jgjkhkjhkjhkjhgkjhgkjhgkjhguyu6yri', '2026-04-08 19:58:56', 1),
(9, '6a04ae501348a.jpg', 'KARLA isdghoifgh´0io gsdógn´dngftm´sdpgj´p', 'NARUTO', '1981-07-16', '065992616665', 'rafaeldinizduarte@gmail.com', NULL, 'pendente', 'KARLA DO CARMO GARCIA DUARTE MECENA', '', 'Da Serra', '', 'VERDE (GRADUADO)', 'INSTRUTOR ESQUILO', '', 'dfhfgtjufyjghkgh hj f ktsjk dtyjk dytj y jus', '2026-05-13 17:00:27', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `id` int(11) NOT NULL,
  `data_aula` date DEFAULT curdate(),
  `docente_id` int(11) NOT NULL,
  `tema_aula` varchar(255) DEFAULT NULL,
  `descricao_atividades` text DEFAULT NULL,
  `local_treino` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `aulas`
--

INSERT INTO `aulas` (`id`, `data_aula`, `docente_id`, `tema_aula`, `descricao_atividades`, `local_treino`) VALUES
(2, '2026-04-09', 12, 'CAPOEIRA LÚDICA', '\"Location: visualizar_aula.php?id=\" . $aula_id);', 'CTS QUILOMBO'),
(4, '2026-04-13', 1, 'CAPOEIRA LÚDICA', '- Sistema de Gestão para Escolas de Capoeira\r\n\r\n15/04/2026 - teste de redirecionamento correto de página de edição', 'CTS QUILOMBO'),
(5, '2026-04-14', 1, 'CAPOEIRA LÚDICA', 'asilduflsiudfgasi çaishfpaoidhfaosi faoidhaoihgoaishg df´goiadfghiadfiog asdogihaóigha gf\r\ngfsodfigpsonifgsiopdnfgosd gdfignafiopgpsdnms~p gfasdfpgj´pj sdpfg fg\r\nsdf\r\ndasfpg´gj´psfg~sdfnsdf\r\nghadflkghjísdfpgh´s\r\n\r\nTESTE DIA 15/04/2026 - BOTÕES DE EDIÇÃO', 'CTS QUILOMBO'),
(6, '2026-04-17', 1, 'CAPOEIRA LÚDICA', 'zfhxdfhdf', 'CTS QUILOMBO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_graduacoes`
--

CREATE TABLE `historico_graduacoes` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `graduacao_anterior` varchar(100) DEFAULT NULL,
  `graduacao_nova` varchar(100) DEFAULT NULL,
  `data_mudanca` date DEFAULT curdate(),
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_graduacoes`
--

INSERT INTO `historico_graduacoes` (`id`, `aluno_id`, `graduacao_anterior`, `graduacao_nova`, `data_mudanca`, `observacao`) VALUES
(1, 1, 'GRADUADO', 'INSTRUTOR', '2026-04-03', 'Atualização de cadastro');

-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `id` int(11) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `status` enum('presente','ausente') DEFAULT 'presente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `presencas`
--

INSERT INTO `presencas` (`id`, `aula_id`, `aluno_id`, `status`) VALUES
(3, 2, 8, 'presente'),
(4, 2, 7, 'presente'),
(19, 5, 1, 'presente'),
(20, 5, 6, 'presente'),
(21, 5, 8, 'presente'),
(22, 5, 7, 'presente'),
(27, 4, 1, 'presente'),
(28, 4, 6, 'presente'),
(29, 4, 8, 'presente'),
(30, 4, 7, 'presente'),
(31, 6, 1, 'presente'),
(32, 6, 6, 'presente'),
(33, 6, 8, 'presente'),
(34, 6, 7, 'presente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','visitante') DEFAULT 'visitante'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `senha`, `nivel`) VALUES
(1, 'admin', '$2y$10$ywu7MUHgJ1L6xDEwe.kxzuccpmnt6iNT8.Y1/LlSAYlJlYx8vZKmC', 'admin'),
(2, 'superadmin', '$2y$10$iZxAoZTlfQcLeOcXfmvQWOIzAvf.r1tqXDO54JccIzIjNZWqB4t1a', 'admin'),
(12, 'CONTRAMESTRE MUTUM', '$2y$10$OlBbrcUnR4w3Fz33ZQ0OveIgwpd92Z1fZU/byV0ik9K52lAWVZ3pi', ''),
(14, 'MESTRE KOSKORAO', '$2y$10$n73NiOtpyAmBhfHh/gKlse/ieAzPYRlnK47D0QCoUZWZWwf28j6XS', 'admin'),
(15, 'MESTRE BIRO', '$2y$10$/T2unjEoR4j2v2idM///VuJZyBn0ep2dK.dFS5VUpzi/420YXWP2m', 'admin'),
(16, 'CONTRAMESTRE CHIQUINHO', '$2y$10$gmHDW8CfuoDu/9Z9gtUFk.7Q98cuiZog3AgU3ohSpWTDQhbKvv3KS', ''),
(17, 'CONTRAMESTRE COYOTE', '$2y$10$8TTbYgGwHpXseSQFFHxafe2Vwk51XcxsLke5Ruv7eNU586L9m3.EW', ''),
(18, 'CONTRAMESTRE AMENDOIN', '$2y$10$Ki4.nSX4PdCs43XLM8sY7eXyCJ5rV.BHkyaVM4q//ikBVpWWdbia2', '');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_docente_aluno` (`usuario_id`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `historico_graduacoes`
--
ALTER TABLE `historico_graduacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices de tabela `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aula_id` (`aula_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `historico_graduacoes`
--
ALTER TABLE `historico_graduacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `fk_docente_aluno` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `historico_graduacoes`
--
ALTER TABLE `historico_graduacoes`
  ADD CONSTRAINT `historico_graduacoes_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
