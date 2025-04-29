function connect(formData) {
    return fetch('../controller/userLogin.php', {
        method: "post",
        body: formData
    })
    .then(response => response.jason());    
   
}

(function(){
    var formlogin = document.querySelector("#form-user");

    formlogin.addEventListener("submit", async function(event){
        event.preventDefault();
        let formData = new FormData(this);

        await connect(formData).then((response) =>{
            console.log(response)
        })
        
        
    });


    /*document.querySelector("#recovery-pass").addEventListener("click", function(event){
        event.preventDefault();
        
        console.log("Clicar no link");
    });*/
})();