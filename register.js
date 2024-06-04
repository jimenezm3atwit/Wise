function checkPassword() {
    let password1 = document.getElementById("password1").value;
    let password2 = document.getElementById("password2").value;
    console.log(password1,password2);

    if(password1.length != 0) {
        if(password1 != password2) {
            alert("Passwords Do Not Match");  
        }
    }
    else {
        alert("Password Cannot Be Empty!");
        result.textContent = "";
    }
}