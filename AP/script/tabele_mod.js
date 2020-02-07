var colID = 1;
function addColumn(){
	var type = ['string', 'integer', 'boolean', 'text'];
	var tresc = "<tr>"+
		"<td><input name='column"+colID+"_name' type='text' placeholder='Nazwa kolumny "+colID+"' class='form-control' /></td>"+
		"<td><input name='column"+colID+"_char' type='number' placeholder='Ilość znaków' min='1' step='1' value='12' class='form-control' /></td>"+
		"<td><select name='column"+colID+"_type' class='form-control'>";
		for (i = 0; i < type.length; i++)
			tresc += "<option value='"+type[i]+"'>"+type[i]+"</option>";
	tresc += "</select></td>"+
	"<td><input name='column_ai' type='radio' value='AI_"+colID+"' class='form-control'></td>"+
	"</tr>";
	$('#columnList').append(tresc);
	colID++;
	return false;
}
addColumn()
addColumn()
addColumn()
