# Curso de Symfony de Udemy ![alt text](https://cdn0.iconfinder.com/data/icons/black-48x48-icons/48/Monkey.png "Solo código")
==========================

Se realizaron e incluyeron los siguientes cambios:

Services/JwtAuth
------------------------
El método checkToken devuelve un objeto con dos propiedades:

 - Valido:  Determina si el token es válido o no.
 - Usuario: Contiene el objeto con la información del usuario que está contenido dentro del token.

Modo de uso:

```php
    $auth = $jwt->checkToken($token);
    if ($auth->valido) {
      #código...
      $id = $auth->usuario->id; // el id es el mismo que en el curso le llama 'sub'
      #código...
    }
```

[Cambios] 
- Siempre retorna el identity si es válido, por lo que siempre estará disponible. Al ser un método que no tiene salida a través de un request no hay riesgos.

- Se eliminó el parámetro identity.

Traits
------------------------
Este traits incluidos, se pueden implementar en cualquier clase entidad de la siguiente forma:

```php
  use Bundle\Clase; // namespaces de otras clases que usa la clase
  
  class Users
  {
    // uses de traits van dentro de la definición de la clase

      use \BackendBundle\Traits\PrePersistTrait; // trait que gestiona el callback del ciclo de vida de la entidad.
      use \BackendBundle\Traits\GeneralTrait; // trait con el método para asignaciones automatizadas.
      ....
  }
```


[PrePersistTrait]
Contiene un método con la anotación @ORM\PrePersist que se ejecuta antes de que se llame al $em->persist($obj). 
A través de este método se asigna la fecha de creación (CreatedAt).

El evento PrePersist se dispara siempre que se inserta, no cuando se actualiza.

El modo de uso es el siguiente:

1. Se incluye en la clase la anotación * @ORM\HasLifecycleCallbacks()
2. Se utiliza la instrucción use para usar el trait:

```php
    /**
    * Users
    *
    * @ORM\Table(name="users")
    * @ORM\Entity
    * @ORM\HasLifecycleCallbacks()
    */
    class Users
    {
      use \BackendBundle\Traits\PrePersistTrait;
      .....

    }
```

Funcionamiento:

```php
    namespace BackendBundle\Traits;

    trait PrePersistTrait{

        /**
        * @ORM\PrePersist <--- Al tener esta anotación el método siempre se llamará antes 
        * de que sea llamado persist en la clase que use el trait
        */
        public function setCreatedAtValue()
        {
            $this->createdAt = new \DateTime(); // asigna la fecha y hora actual al campo createat
        }
    }
```


[GeneralTrait]

Este trait fue creado para incluir métodos comunes a todas las clases entidad. Sólo contiene un método llamado autoset.

El método autoset se encarga de realizar los set automáticamente sin tener que programarlos explícitamente, sólo recibiendo el json:

Antes:

```php
    $email = (isset($json->email)) ? $json->email : null;
    $name = (isset($json->name)) ? $json->name : null;
    $surname = (isset($json->surname)) ? $json->surname : null;
    $password = (isset($json->password)) ? $json->password : null;
    $rol = (isset($json->rol)) ? $json->rol : null;

    $user = new Users();
    
    $user->setName($name);
    $user->setEmail($email);
    $user->setPassword($password);
    $user->setRole($rol);
    $user->setSurname($surname);
```
Con autoSet:
```php
    $user = new Users();
    $user->autoSet($json);
```

Modo de uso:

1. Incluir el trait en la entidad en la cual se va a usar:

```php
    // clase users del curso
    
    class Users
    {
        use \BackendBundle\Traits\GeneralTrait;
        .....
    }
```

2. Dentro del trait modificar el atributo entityBundle con el nombre del Bundle donde están las entidades:

```php
    // declaración del trait
    trait GeneralTrait{
        
        private $entityBundle='BackendBundle'; // la carpeta Entity está en BackendBundle
        ....
    }
```

3. Para llamar al método se requieren los parámetros:

  - json (Requerido):   objeto decodificado de json que contiene los atributos que se van a asignar. 
  - em (opcional):      instancia de EntityManager. Requerido en caso de insertar un objeto con relación (fkid).
  - ignorar (opcional): array con los nombres de los atributos que se deben ignorar en la asignación.

4. Luego de instanciar la entidad, se puede llamar la función como una función nativa de la entidad.

```php
    // creando una entidad....

    $usuario = new Usuario();
    $usuario->autoSet($json);

    // editando una entidad....

    $usuario = $em->getRepository('Usuario')->find($pkidusuario);
    $usuario->autoSet($json);

    // creando una entidad con relación ....
    
    $em = $this->getDoctrine()->getManager();
    $usuario = new Usuario();

    // json contiene un atributo fkidrol el cual tiene un id (no un objeto) que apunta al pkid del rol
    // que se asignará. el método autoset se encargará de buscarlo en la bd y asignarlo automáticamente.
    // Para que pueda ser buscado debe pasarse la instancia de EntityManager ya existente.
    
    $usuario->autoSet($json, $em); 

    //ignorando campos para evitar sobreescritura o errores:
    //se ignorará (no serán asignados) dentro del método los campos permisos y password.
    
    $usuario->autoSet($json, null, array('permisos', 'password')); 

    //gestionando lógica de los campos ya asignados:
    $usuario->autoSet($json); //se asigna nombre sin problema
    $usuario->setNombre($nombre); //se reasigna nombre.

```