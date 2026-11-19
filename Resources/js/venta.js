function cargarValor(){
	var form = document.frm1;
	var id = form.producto.value;
	
	if (id == "1") {
		form.valor.value="12500";
	}else if(id == "2"){
		form.valor.value="15800";
	}else if(id == "3"){
		form.valor.value="45200";
	}else if(id == "4"){
		form.valor.value="57800";
	}else{
		alert("debe seleccionar un producto");
		form.valor.value="";
	}
}
class Product{
	constructor(producto,valor,cantidad,subtotal){
		this.producto = producto;
		this.valor = valor;
		this.cantidad = cantidad;
		this.subtotal = subtotal;
	}
}
class UI{
	addProducto(product){
		  var tr = `
			<tr>
				<td>${product.producto}</td>
				<td>${product.valor}</td>
				<td>${product.cantidad}</td>
				<td>${product.subtotal}</td>
				<td><button>Editar</button></td>
				<td><button>Eliminar</button></td>
			</tr>
		  `;
		  $("#bodiL").append(tr);
				/*var elmtTable = document.getElementById('bodiL');
				var tableRows = elmtTable.getElementsByTagName('tr');
				var rowCount = tableRows.length;
				alert(rowCount);
				alert(tableRows);
				alert(elmtTable);

				for (var i = 0; i < rowCount.length; i++) {
					alert(product.subtotal);
				}*/
				var filas=document.querySelectorAll("#table1 tbody tr");
 
			    var total=0;
			 
			    // recorremos cada una de las filas
			    filas.forEach(function(e) {
			 
			        // obtenemos las columnas de cada fila
			        var columnas=e.querySelectorAll("td");
			 
			        // obtenemos los valores de la cantidad y importe
			        var cantidad=parseFloat(columnas[1].textContent);
			        var importe=parseFloat(columnas[2].textContent);
			 
			        // mostramos el total por fila
			        columnas[3].textContent=(cantidad*importe).toFixed(2);
			 
			        total+=cantidad*importe;

			    });
			 	document.getElementById("subtotal").value=total;
			 	document.getElementById("iva").value=(parseInt(total)*19)/100;
			 	document.getElementById("total").value=parseInt(total)+parseInt(document.getElementById("iva").value);
			    // mostramos la suma total
			    
				

	}

}
function eliminarList(){
	var elmtTable = document.getElementById('bodiL');
	var tableRows = elmtTable.getElementsByTagName('tr');
	var rowCount = tableRows.length;

	for (var x=rowCount+1; x>0; x--) {
	      elmtTable.remove(tableRows[x]);
	}
	location.reload();
}
function vender(){
	Swal
    .fire({
        title: "Venta #123465",
        text: "¿Registrar Venta?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "Cancelar",
    })
    .then(resultado => {
        if (resultado.value) {
            // Hicieron click en "Sí"
            console.log("*se elimina la venta*");
            location.reload();
        } else {
            // Dijeron que no
            console.log("*NO se elimina la venta*");
        }
    });

	//location.reload();
}
function masage(){
	location.reload();
}
document.getElementById('frm1',addEventListener('submit', function(e){
	var combo = document.getElementById("producto");
	var selected = combo.options[combo.selectedIndex].text;
	const producto = selected;
	const valor = document.getElementById('valor').value;
	const cantidad = document.getElementById('cantidad').value;
	const subtotal = valor*cantidad;
	const product = new Product(producto,valor,cantidad,subtotal);

	const ui = new UI();
	ui.addProducto(product);

	
	//console.log(producto,valor,cantidad);
	//console.log(new Product(producto,valor,cantidad));

	e.preventDefault();

}))