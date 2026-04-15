function registerUser(){

let name=document.getElementById("regName").value;
let email=document.getElementById("regEmail").value;
let password=document.getElementById("regPassword").value;
let role=document.getElementById("regRole").value;

if(name===""||email===""||password===""){
alert("Fill All Fields");
return;
}

localStorage.setItem("userName",name);
localStorage.setItem("userEmail",email);
localStorage.setItem("userPassword",password);
localStorage.setItem("userRole",role);

alert("Registered Successfully");

window.location.href="login.html";
}



function loginUser(){

let email=document.getElementById("loginEmail").value;
let password=document.getElementById("loginPassword").value;

let storedEmail=localStorage.getItem("userEmail");
let storedPassword=localStorage.getItem("userPassword");
let role=localStorage.getItem("userRole");

if(email===storedEmail&&password===storedPassword){

if(role==="freelancer"){
window.location.href="freelancer-dashboard.html";
}
else{
window.location.href="client-dashboard.html";
}

}
else{
alert("Invalid Credentials");
}

}



function logoutUser(){
window.location.href="index.html";
}