// =========================
// BASE URL (ADDED)
// =========================
const BASE_URL = "http://localhost/FreelanceMarketplace/";


// =========================
// REGISTER USER (DB BASED)
// =========================
function registerUser(){

    let name = document.getElementById("regName").value.trim();
    let email = document.getElementById("regEmail").value.trim();
    let password = document.getElementById("regPassword").value.trim();
    let role = document.getElementById("regRole").value;

    if(name === "" || email === "" || password === ""){
        alert("Fill All Fields");
        return;
    }

    fetch(BASE_URL + "register.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({ name, email, password, role }).toString()
    })
    .then(res => res.text())
    .then(data => {

        console.log("REGISTER RESPONSE:", data);

        data = data.trim();

        if(data === "success"){
            alert("Registered Successfully");
            window.location.href = "login.html";
        }
        else{
            alert("Error: " + data);
        }

    })
    .catch(err => {
        console.error("REGISTER ERROR:", err);
    });
}


// =========================
// LOGIN USER + SESSION
// =========================
function loginUser(){

    let email = document.getElementById("loginEmail").value.trim();
    let password = document.getElementById("loginPassword").value.trim();

    if(email === "" || password === ""){
        alert("Please enter email and password");
        return;
    }

    fetch(BASE_URL + "login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `email=${email}&password=${password}`
    })
    .then(res => res.text())
    .then(data => {

        console.log("LOGIN RESPONSE:", data);

        data = data.trim();

        if(data === "freelancer"){
            localStorage.setItem("loggedInUser", email);
            localStorage.setItem("role", "freelancer");
            window.location.href = "freelancer-dashboard.php";
        }
        else if(data === "client"){
            localStorage.setItem("loggedInUser", email);
            localStorage.setItem("role", "client");
            window.location.href = "client-dashboard.php";
        }
        else{
            alert("Invalid Credentials");
        }

    })
    .catch(err => {
        console.error("LOGIN ERROR:", err);
    });
}


// =========================
// LOGOUT
// =========================
function logoutUser(){
    localStorage.removeItem("loggedInUser");
    localStorage.removeItem("role");
    window.location.href = "login.html";
}


// =========================
// CHECK LOGIN (PROTECT PAGE)
// =========================
function checkLogin(requiredRole){

    let user = localStorage.getItem("loggedInUser");
    let role = localStorage.getItem("role");

    if(!user){
        window.location.href = "login.html";
        return;
    }

    if(requiredRole && role !== requiredRole){
        alert("Unauthorized Access");
        window.location.href = "login.html";
    }
}


// =========================
// POST PROJECT (CLIENT)
// =========================
function postProject(){

    let title = document.getElementById("projectTitle").value.trim();
    let description = document.getElementById("projectDesc").value.trim();
    let budget = document.getElementById("projectBudget").value.trim();
    let email = localStorage.getItem("loggedInUser");

    if(title === "" || description === "" || budget === ""){
        alert("Fill all fields");
        return;
    }

    fetch(BASE_URL + "post_project.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `title=${title}&description=${description}&budget=${budget}&email=${email}`
    })
    .then(res => res.text())
    .then(data => {

        console.log("POST PROJECT:", data);

        if(data.trim() === "success"){
            alert("Project Posted Successfully");
            document.getElementById("projectTitle").value = "";
            document.getElementById("projectDesc").value = "";
            document.getElementById("projectBudget").value = "";
        }else{
            alert("Error posting project");
        }

    });
}


// =========================
// LOAD APPLICATIONS (CLIENT) [IMPROVED]
// =========================
function loadApplications(){

    let email = localStorage.getItem("loggedInUser");

    if(!email){
        document.getElementById("applicationsContainer").innerHTML =
        "<p style='color:red;'>User not logged in</p>";
        return;
    }

    fetch(BASE_URL + `get_applications.php?email=${email}`)
    .then(res=>res.json())
    .then(data=>{

        let container = document.getElementById("applicationsContainer");

        if(!data || data.length===0){
            container.innerHTML = "<p>No Applications</p>";
            return;
        }

        let html="";

        data.forEach(a=>{
            html+=`
            <div class="project-card">
                <h3>${a.title}</h3>
                <p><b>Freelancer:</b> ${a.freelancer_email}</p>
                <p>${a.proposal}</p>

                <button onclick="updateStatus(${a.id},'accepted')">Accept</button>
                <button onclick="updateStatus(${a.id},'rejected')">Reject</button>
            </div>
            `;
        });

        container.innerHTML = html;

    })
    .catch(err=>{
        console.error("APPLICATION LOAD ERROR:", err);
        document.getElementById("applicationsContainer").innerHTML =
        "<p style='color:red;'>Error loading applications</p>";
    });
}


// =========================
// LOAD PROJECTS (FREELANCER) [IMPROVED]
// =========================
function loadProjects(){

    fetch(BASE_URL + "get_projects.php")
    .then(res => res.json())
    .then(data => {

        let container = document.getElementById("projectsContainer");

        if(!data || data.length === 0){
            container.innerHTML = "<p>No Projects Available</p>";
            return;
        }

        let html = "";

        data.forEach(p => {

            let email = localStorage.getItem("loggedInUser") || "guest";
            let key = "applied_" + p.id + "_" + email;

            let applied = localStorage.getItem(key);

            html += `
                <div class="project-card">
                    <h3>${p.title}</h3>
                    <p>${p.description}</p>
                    <p><b>Budget:</b> ₹${p.budget}</p>

                    <button 
                        onclick="applyProject(${p.id})"
                        ${applied ? "disabled" : ""}
                        style="margin-top:10px;padding:8px 12px;
                        background:${applied ? "#aaa" : "#6c5ce7"};
                        color:white;border:none;border-radius:5px;cursor:pointer;">
                        ${applied ? "Applied" : "Apply"}
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;

    })
    .catch(err => {
        console.error("LOAD PROJECT ERROR:", err);
        document.getElementById("projectsContainer").innerHTML =
        "<p style='color:red;'>Error loading projects</p>";
    });
}


// =========================
// APPLY PROJECT (IMPROVED)
// =========================
function applyProject(projectId){

    let email = localStorage.getItem("loggedInUser");

    if(!email){
        alert("Please login first");
        return;
    }

    let key = "applied_" + projectId + "_" + email;

    if(localStorage.getItem(key)){
        alert("Already applied");
        return;
    }

    let proposal = prompt("Enter your proposal");

    if(!proposal || proposal.trim()===""){
        alert("Proposal required");
        return;
    }

    fetch(BASE_URL + "apply_project.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body:`project_id=${projectId}&email=${email}&proposal=${proposal}`
    })
    .then(res=>res.text())
    .then(data=>{
        if(data.trim()==="success"){
            alert("Applied successfully");
            localStorage.setItem(key, "true");
            loadProjects();
        }else{
            alert("Error applying");
        }
    })
    .catch(err=>{
        console.error("APPLY ERROR:", err);
    });
}


// =========================
// UPDATE STATUS (UNCHANGED)
// =========================
function updateStatus(id,status){

fetch(BASE_URL + "update_status.php",{
method:"POST",
headers:{
"Content-Type":"application/x-www-form-urlencoded"
},
body:`id=${id}&status=${status}`
})
.then(res=>res.text())
.then(data=>{
if(data.trim()==="success"){
alert("Updated");
location.reload();
}
});
}
