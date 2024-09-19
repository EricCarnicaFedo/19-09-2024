<?php
include('db.php');
session_start();

// Função para preencher o select de estados
function getEstados($pdo) {
    $sql = "SELECT id, nome FROM estados ORDER BY nome";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para preencher o select de cidades baseado no estado
function getCidades($pdo, $estado_id) {
    $sql = "SELECT id, nome FROM cidades WHERE estado_id = ? ORDER BY nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$estado_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    $tipo = $_POST['tipo'];
    $clinica_id = $_POST['clinica_id'] ?? null;  // Pegamos o 'clinica_id' do formulário, se existir

    try {
        // Verificar se o email já está cadastrado
        $stmt_check_email = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt_check_email->execute([$email]);
        $email_count = $stmt_check_email->fetchColumn();

        if ($email_count > 0) {
            echo "Erro: O email já está cadastrado!";
            exit();
        }

        // Se for veterinário e ele está criando uma clínica
        if ($tipo == 'veterinario' && !empty($_POST['clinica_nome'])) {
            $clinica_nome = $_POST['clinica_nome'];
            $clinica_endereco = $_POST['clinica_endereco'];
            
            // Inserir a nova clínica no banco de dados
            $sql_clinica = "INSERT INTO clinicas (nome, endereco) VALUES (?, ?)";
            $stmt_clinica = $pdo->prepare($sql_clinica);
            $stmt_clinica->execute([$clinica_nome, $clinica_endereco]);

            // Pega o ID da nova clínica criada
            $clinica_id = $pdo->lastInsertId();
        }

        // Se o usuário for tutor, ele deve selecionar uma clínica
        if ($tipo == 'tutor' && empty($clinica_id)) {
            echo "Erro: o tutor precisa selecionar uma clínica!";
            exit();
        }

        // Verifique se o clinica_id é válido (existe na tabela clinicas)
        if ($clinica_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM clinicas WHERE id = ?");
            $stmt->execute([$clinica_id]);
            $clinica_count = $stmt->fetchColumn();
            
            if ($clinica_count == 0) {
                echo "Erro: O clinica_id não corresponde a nenhuma clínica existente!";
                exit();
            }
        }

        // Inserir o usuário no banco de dados
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo, clinica_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $senha, $tipo, $clinica_id]);

        // Verificar se o usuário é um tutor
        if ($tipo == 'tutor') {
            // Adicionar o tutor como cliente
            $telefone = $_POST['telefone'] ?? null;
            $endereco = $_POST['endereco'] ?? null;
            $cidade_id = $_POST['cidade_id'] ?? null;
            $estado_id = $_POST['estado_id'] ?? null;
            $cep = $_POST['cep'] ?? null;

            $sql_cliente = "INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, cep, clinica_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_cliente = $pdo->prepare($sql_cliente);
$stmt_cliente->execute([$nome, $email, $telefone, $endereco, $_POST['cidade'], $_POST['estado'], $cep, $clinica_id]);


            // Adicionar o tutor na tabela tutores
            $id_cliente = $pdo->lastInsertId();  // Usar o último ID inserido na tabela clientes
            $sql_tutor = "INSERT INTO tutores (nome, email, clinica_id) VALUES (?, ?, ?)";
            $stmt_tutor = $pdo->prepare($sql_tutor);
            $stmt_tutor->execute([$nome, $email, $clinica_id]);
        }

        echo "Usuário cadastrado com sucesso!";
    } catch (PDOException $e) {
        // Mensagem de erro caso ocorra uma exceção
        echo "Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="styles.css">
    <link href='https://unpkg.com/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="form-container">
        <form method="POST" action="cadastro.php">
            <h1><i class='bx bxs-user'></i> Cadastro</h1>

            <label for="nome"><i class='bx bxs-user'></i> Nome:</label>
            <input type="text" name="nome" id="nome" placeholder="Digite seu nome" required>

            <label for="email"><i class='bx bxs-envelope'></i> Email:</label>
            <input type="email" name="email" id="email" placeholder="Digite seu email" required>

            <label for="senha"><i class='bx bxs-lock-alt'></i> Senha:</label>
            <input type="password" name="senha" id="senha" placeholder="Digite sua senha" required>

            <label for="tipo"><i class='bx bxs-briefcase-alt-2'></i> Selecione sua função:</label>
            <select name="tipo" id="tipo" required>
                <option value="" disabled selected>Selecione sua função</option>
                <option value="tutor">Tutor</option>
                <option value="veterinario">Veterinário</option>
            </select>

            <div id="clinica-section">
                <div id="nova-clinica-section" style="display: none;">
                    <h3><i class='bx bxs-clinic'></i> Cadastrar Nova Clínica</h3>
                    <label for="clinica_nome">Nome da Clínica:</label>
                    <input type="text" name="clinica_nome" id="clinica_nome" placeholder="Nome da clínica">

                    <label for="clinica_endereco">Endereço:</label>
                    <input type="text" name="clinica_endereco" id="clinica_endereco" placeholder="Endereço da clínica">
                </div>

                <label for="clinica" id="clinica-label" style="display: none;">Clínica:</label>
                <select name="clinica_id" id="clinica" style="display: none;">
                    <option value="">Selecione uma clínica</option>
                    <?php
                    // Exibir clínicas cadastradas
                    $clinicas = $pdo->query("SELECT id, nome FROM clinicas")->fetchAll();
                    foreach ($clinicas as $clinica) {
                        echo "<option value='{$clinica['id']}'>{$clinica['nome']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="cliente-info-section" style="display: none;">
                <label for="telefone"><i class='bx bxs-phone'></i> Telefone:</label>
                <input type="text" name="telefone" id="telefone" placeholder="Digite seu telefone">

                <label for="endereco"><i class='bx bxs-home'></i> Endereço:</label>
                <input type="text" name="endereco" id="endereco" placeholder="Digite seu endereço">

                <label for="cidade"><i class='bx bxs-city'></i> Cidade:</label>
                <select name="cidade" id="cidade">
                    <option value="">Selecione uma cidade</option>
                    <?php
                    // Exibir cidades cadastradas
                    $cidades = $pdo->query("SELECT id, nome FROM cidades")->fetchAll();
                    foreach ($cidades as $cidade) {
                        echo "<option value='{$cidade['id']}'>{$cidade['nome']}</option>";
                    }
                    ?>
                </select>

                <label for="estado"><i class='bx bxs-map'></i> Estado:</label>
                <select name="estado" id="estado">
                    <option value="">Selecione um estado</option>
                    <?php
                    // Exibir estados cadastrados
                    $estados = $pdo->query("SELECT id, nome FROM estados")->fetchAll();
                    foreach ($estados as $estado) {
                        echo "<option value='{$estado['id']}'>{$estado['nome']}</option>";
                    }
                    ?>
                </select>

                <label for="cep"><i class='bx bx-map-pin'></i> CEP:</label>
                <input type="text" name="cep" id="cep" placeholder="Digite seu CEP">
            </div>

            <button type="submit" class="submit-btn"><i class='bx bx-check'></i> Cadastrar</button>

            <p class="redirect-info">Já tem uma conta? <a href="login.php" class="redirect-link">Faça login aqui</a></p>
        </form>
    </div>
    <script>
        document.getElementById('tipo').addEventListener('change', function() {
            var tipo = this.value;
            var clinicaSection = document.getElementById('clinica-section');
            var novaClinicaSection = document.getElementById('nova-clinica-section');
            var clinicaSelect = document.getElementById('clinica');
            var clinicaLabel = document.getElementById('clinica-label');
            var clienteInfoSection = document.getElementById('cliente-info-section');

            if (tipo === 'veterinario') {
                clinicaSection.style.display = 'block';
                novaClinicaSection.style.display = 'block'; // Exibe campos para nova clínica
                clinicaSelect.style.display = 'none'; // Esconde seletor de clínica
                clinicaLabel.style.display = 'none'; // Esconde rótulo do seletor de clínica
                clienteInfoSection.style.display = 'none'; // Esconde informações do cliente
            } else if (tipo === 'tutor') {
                clinicaSection.style.display = 'block';
                novaClinicaSection.style.display = 'none'; // Esconde campos para nova clínica
                clinicaSelect.style.display = 'block'; // Exibe seletor de clínica
                clinicaLabel.style.display = 'block'; // Exibe rótulo do seletor de clínica
                clienteInfoSection.style.display = 'block'; // Exibe informações do cliente
            }
        });
    </script>
</body>
</html>

