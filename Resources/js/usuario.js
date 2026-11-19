var users = [
  {
    nombre: "admin",
    apellido: "Administrador",
    direccion: "cr 20# 10-15",
    telefono: 5675021,
    permiso: "Administrador"
    
  },
  {
    nombre: "Vendedor",
    apellido: "vend",
    direccion: "cr 20# 10-30",
    telefono: 3102543636,
    permiso: "Vendedor"
  }
];
class Usuario{
  constructor(nombre,apellido,direccion,telefono,permiso){
    this.nombre = nombre;
    this.apellido = apellido;
    this.direccion = direccion;
    this.telefono = telefono;
    this.permiso = permiso;
  }
}
class UI{

  addUsuario(usuario){
      var tr = `
      <tr>
        <td>${usuario.nombre}</td>
        <td>${usuario.apellido}</td>
        <td>${usuario.direccion}</td>
        <td>${usuario.telefono}</td>
        <td>${usuario.permiso}</td>
        
        <td><button class="btn btn-danger" onclick="deleteRow(this)">Eliminar</button></td>
      </tr>
      `;
      $("#bodiL").append(tr);
  }


}
document.getElementById('form1',addEventListener('submit', function(e){
  var combo = document.getElementById("permiso");
  var selected = combo.options[combo.selectedIndex].text;
  const permiso = selected;
  const nombre = document.getElementById('nombre').value;
  const apellido = document.getElementById('apellido').value;
  const direccion = document.getElementById('direccion').value;
  const telefono = document.getElementById('telefono').value;

  const usuario = new Usuario(nombre,apellido,direccion,telefono,permiso);
if (permiso == "Permiso") {
  alert("debe seleccionar un permiso valido");
}else{
  const ui = new UI();
  ui.addUsuario(usuario);
  console.log(usuario);
}
  
  
  //console.log(producto,valor,cantidad);
  //console.log(new Product(producto,valor,cantidad));

  e.preventDefault();

}));
function deleteRow(row)
{
    var i=row.parentNode.parentNode.rowIndex;
    document.getElementById('userTable').deleteRow(i);
}