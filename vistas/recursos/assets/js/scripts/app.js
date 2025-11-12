var inicializarhor=function(){

	var fechaActual= new Date();
	var tiempoHoras= fechaActual.getHours();
	var tiempoMinutos=fechaActual.getMinutes();
	var tiempoSegundos=fechaActual.getSeconds();

	var mesActual= fechaActual.getMonth();
	var diaActual=fechaActual.getDay();
	var diaDelMes=fechaActual.getDate();
	var aActual=fechaActual.getFullYear();
	var amOpm;

	var meses=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Juilo","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
	var esteMes= meses[mesActual];

	var diasDeLaSemana =["Domingo","Lunes","Martes","Miercoles","jueves","Viernes","Sabado"];
	var diaDeHoy=diasDeLaSemana[diaActual];

	amOpm = (tiempoHoras>12)? "pm" : "am";
	tiempoHoras= (tiempoHoras>12)?tiempoHoras-12: tiempoHoras;
	tiempoHoras= (tiempoHoras<10)?"0"+tiempoHoras:tiempoHoras; 
	tiempoMinutos=(tiempoMinutos<10)?"0"+tiempoMinutos:tiempoMinutos;
	tiempoSegundos=(tiempoSegundos<10)?"0"+tiempoSegundos:tiempoSegundos;
    document.getElementById("lafecha").innerHTML= diaDeHoy+" , "+diaDelMes+ " de " + esteMes +" del "+ aActual
	document.getElementById("info").innerHTML=tiempoHoras+" : "+tiempoMinutos+" : "+tiempoSegundos+ amOpm;

}
inicializarhor();
setInterval(inicializarhor,1000);