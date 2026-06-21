function pushHideButton(id) {
    var txtPass = document.getElementById(id);
    var btnEye = document.getElementById("buttonEye-"+id);
    if (txtPass.type === "text") {
        txtPass.type = "password";
        btnEye.className = "fa-solid fa-eye-slash";
    } else {
        txtPass.type = "text";
        btnEye.className = "fa-solid fa-eye";
    }
}