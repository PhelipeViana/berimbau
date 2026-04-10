<?php
// Escolha a senha que deseja
$senha_pura = '123456'; 

// Gera o código criptografado
$senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

echo "Crie o usuário no banco com este hash:<br>";
echo "<strong>" . $senha_hash . "</strong>";
?>