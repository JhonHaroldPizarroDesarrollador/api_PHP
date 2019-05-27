<?php
//INCLUIR AUTOLOAD DE COMPOSER
require_once 'vendor/autoload.php';
$app = new \Slim\Slim();
//CONEXION A LA DB INSTANCIANDO EL OBJETO MYSQL
$db = new mysqli('localhost', 'root', 'root', 'adc_app');
//CONFIGURACIONDE CABECERAS
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}
$app->get("/pruebas", function() use($app, $db){
    echo "Hola mundo desde slim php";
    var_dump($db);
});
// LISTAR PRODUCTOS
$app->get('/productos', function() use($db, $app){
    //CONSULTA SQL
    $sql = 'SELECT * FROM productos ORDER BY id DESC;';
    //EJECUTAR CONSULTA UTILIZANDO EL OBJETO DB
    $query = $db->query($sql);
    //CREAR LA COLECCION DE OBJETOS 
    $productos = array();
    while($producto = $query->fetch_assoc()){
        $productos[] = $producto;
    }
    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos
    );
    echo json_encode($result);
});
// DEVOLVER PRODUCTO
$app->get('/producto/:id', function($id) use($db, $app){
    $sql = 'SELECT * FROM productos WHERE id = '.$id;
    $query = $db->query($sql);
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto No Disponible'
    );
    if($query->num_rows == 1){
        $producto = $query->fetch_assoc();
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto
        );
    }
    echo json_encode($result);
});
// ELIMINAR PRODUCTO
$app->get('/delete-producto/:id', function($id) use($db, $app){
    $sql = 'DELETE FROM productos WHERE id = '. $id;
    $query = $db->query($sql);

    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Producto Borrado Correctamente',
        );
    }
    else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Producto No Borrado',
        );        
    }
    echo json_encode($result);
});
// ACTUALIZAR PRODUCTO
$app->post('/update-producto/:id', function($id) use($db, $app){
    $json = $app->request->post('json');
    $data = json_decode($json, true);
    $sql =  "UPDATE productos SET ".
            "nombre = '{$data["nombre"]}', ".
            "descripcion = '{$data["descripcion"]}', ";
    if(isset($data['imagen'])) {
        $sql .= "imagen = '{$data["imagen"]}', ";
    } 
    else {
        # code...
    }
    $sql .=  "precio = '{$data["precio"]}', descuento = '{$data["descuento"]}', inicio_descuento = '{$data["inicio_descuento"]}', fin_descuento = '{$data["fin_descuento"]}' WHERE id = {$id}";
    $query = $db->query($sql);
    //var_dump($sql);
    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Producto Actualizado Correctamente',
        );
    }
    else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Producto No Actualizado',
        );
    }
    echo json_encode($result);
});
// SUBIR IMAGEN AL PRODUCTO
$app->post('/upload-file', function() use($db, $app){
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'El archivo no se ha subido',
    );
    if(isset($_FILES['uploads'])){
        //echo "Llegan los datos ";
        $piramideUploader = new PiramideUploader();
        $upload = $piramideUploader->upload('image','uploads', 'uploads', array('image/jpeg', 'image/png', 'image/gif'));
        $file = $piramideUploader->getInfoFile();
        $file_name = $file['complete_name'];
        if(isset($upload) && $upload['uploaded'] == false){
            $result = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El archivo no se ha subido',
            );
        }
        else{
            $result = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El archivo se ha subido correctamente',
                'filename' => $file_name
            );
        }
    }
    echo json_encode($result);
});
// GUARDAR PRODUCTOS
$app->post('/productos', function() use($app, $db){
    $json = $app->request->post('json');
    $data = json_decode($json, true);
    //VALIDAD SI EXISTEN LOS DATOS
    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }
    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }
    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }
    if (!isset($data['descuento'])) {
        $data['descuento'] = null;
    }
    if (!isset($data['inicio_descuento'])) {
        $data['inicio_descuento'] = null;
    }
    if (!isset($data['fin_descuento'])) {
        $data['fin_descuento'] = null;
    }
    if(!isset($data['imagen'])){
        $data['imagen']=null;
    }    
    //CONSULTA SQL
    $query = "INSERT INTO productos VALUES(NULL,".
                "'{$data['nombre']}',".
                "'{$data['descripcion']}',".
                "'{$data['precio']}',".
                "'{$data['descuento']}',".
                "'{$data['inicio_descuento']}',".
                "'{$data['fin_descuento']}',".
                "'{$data['imagen']}'".
              ")";
    //EJECUTAR CONSULTA UTILIZANDO EL OBJETO DB
    $insert = $db->query($query);
    //VALIDAR SI EL PRDUCTO SE INSERTO A LA DB
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'Producto NO se ha creado'
    );
    if($insert){
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Producto creado correctamente'
        );
    }
    //RESPUESTA JSON
    echo json_encode($result);
});
// run app
$app->run();