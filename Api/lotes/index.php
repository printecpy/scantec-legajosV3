<?php
header("Content-Type: application/json; charset=UTF-8");
include "../../Config/Config.php";
include "../utils.php";
$dbConn =  connect($db);
if (!isset($_SERVER['PHP_AUTH_USER'])) {
  header('WWW-Authenticate: Basic realm="Zona Privada"');
  header('HTTP/1.0 401 Unauthorized');
  echo json_encode(['error' => 'Las credenciales son incorrectas.']);
  exit;
} else {
  $usuario = $_SERVER['PHP_AUTH_USER'];
  $clave = $_SERVER['PHP_AUTH_PW'];

  // Convertir la contraseña a SHA512
  $clave_hash = hash("SHA512", $clave);

  // Consulta a la base de datos para verificar las credenciales
  $stmt = $dbConn->prepare('SELECT * FROM usuarios WHERE usuario = :usuario AND clave = :clave');
  $stmt->bindParam(':usuario', $usuario);
  $stmt->bindParam(':clave', $clave_hash);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result) {
      // Las credenciales son correctas, el usuario puede acceder al sistema API
      echo json_encode(['OK' => 'Acceso permitido.  ']);
      // Aquí puedes agregar el resto de tu código
/*
  listar todos los lote o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['id']))
    {
      //Mostrar un lote
      $sql = $dbConn->prepare("SELECT * FROM lote where id_registro=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
	  }
    else {
      //Mostrar lista de lote
      $sql = $dbConn->prepare("SELECT * FROM lote");
      $sql->execute();
      $sql->setFetchMode(PDO::FETCH_ASSOC);
      header("HTTP/1.1 200 OK");
      echo json_encode( $sql->fetchAll()  );
      exit();
	}
}
// Crear un nuevo lote
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $input = $_POST;
    $sql = "INSERT INTO lote
          (title, status, content, user_id)
          VALUES
          (:title, :status, :content, :user_id)";
    $statement = $dbConn->prepare($sql);
    bindAllValues($statement, $input);
    $statement->execute();
    $postId = $dbConn->lastInsertId();
    if($postId)
    {
      $input['id'] = $postId;
      header("HTTP/1.1 200 OK");
      echo json_encode($input);
      exit();
	 }
}
//Borrar lote
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
	$id = $_GET['id'];
  $statement = $dbConn->prepare("DELETE FROM lote where id_registro=:id");
  $statement->bindValue(':id', $id);
  $statement->execute();
	header("HTTP/1.1 200 OK");
  echo json_encode(['Ok' => 'Registro eliminado correctamente']);
	exit();
}
//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{
    $input = $_GET;
    $postId = $input['id'];
    $fields = getParams($input);
    $sql = "
          UPDATE lote
          SET $fields
          WHERE id_registro='$postId'
           ";
    $statement = $dbConn->prepare($sql);
    bindAllValues($statement, $input);
    $statement->execute();
    header("HTTP/1.1 200 OK");
    echo json_encode(['Ok' => 'Registro eliminado correctamente']);
    exit();
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Solicitud incorrecta");
echo json_encode(['error' => 'Solicitud incorrecta']);

} else {
  // Las credenciales son incorrectas, se niega el acceso
  header('WWW-Authenticate: Basic realm="Zona Privada"');
  header('HTTP/1.0 401 Unauthorized');
  echo json_encode(['error' => 'Las credenciales son incorrectas.']);
  exit;
}
}
?>