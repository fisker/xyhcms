function subinit()
{
  if (form1.username.value=="")
   { 
	alert("��û��������");
	document.form1.username.focus();
	return false;
	 }
  if (form1.tel.value=="")
   { 
	alert("��û����绰��QQ��");
	document.form1.tel.focus();
	return false;
	 }
  if (form1.todate.value=="")
   { 
	alert("��û�����������");
	document.form1.todate.focus();
	return false;
	 }
	if (form1.disease.value=="")
   { 
	alert("��û�����");
	document.form1.disease.focus();
	return false;
	 }
  if (form1.doctor.value=="")
   { 
	alert("��û����ר��");
	document.form1.doctor.focus();
	return false;
	 }
return true;
  }