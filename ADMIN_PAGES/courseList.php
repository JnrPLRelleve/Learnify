<?php
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: loginpage_Admin.php');
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List</title>
    <link rel="stylesheet" href="courseList.css">
    <style>
        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f8f9fa; 
            padding: 40px;
            width: 80%;
            max-width: 900px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            border-radius: 8px;
            font-family: Arial, sans-serif;
        }

        .modal-content h1 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .modal-content p {
            font-size: 1rem;
            margin: 10px 0;
        }

        .btn5 {
            background-color: #ff4d4d; 
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn5:hover {
            background-color: #e60000;
        }

        .btn6 {
            background-color: #d3d3d3; 
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn6:hover {
            background-color: #a9a9a9;
        }

        .modal-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .modal-content .header3 {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .modal-content .p4 {
            align-self: flex-end;
            font-size: 1rem;
            font-style: italic;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="box1">
        <div class="box2">
          <h1 class="heading">ADMIN</h1>
                <button class="btn1" onclick="location.href='courseList.php'"> Courses </button>
                <button class="btn2" onclick="location.href='instructorList.php'"> Instructors </button>
                <button class="btn3" onclick="location.href='studentList.php'"> Students</button>
                <button class="btn4" onclick="location.href='admin_List.php'"> Admin List</button>
                <button class="btn5" type="button" onclick="window.location.href='admin_Logout.php'">Logout</button>
                <img class="image1" src="/images/AdminPerson.jpg" alt="image"></img>
      
        </div> 
        <div class="box3"> 
          
          <label class="labelSearch" for="search">Search: </label>
          <div class="search-wrap">
           <input class="inputSearch" type="search" id="search" placeholder="Sort by...."></div>
          
           <div class=" box4">
              <div class="user-cards">
                <div class="card">
                  <div class="header3">Course Name1</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name2</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name3</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name4</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name5</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name6</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name7</div>
                </div>

                <div class="card">
                  <div class="header3">Course Name8</div>

               </div>
             </div>

            </div>
         
          
          
     
        </div>

        <!-- modal -->
        <div id="modalOverlay" class="modal-overlay" style="display: none;" onclick="closeModal()"></div>
        <div id="courseModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h1 class="header3">Course Name</h1>
                <p class="p2">Section</p>
                <p class="p3">Lorem ipsum dolor sit amet consectetur adipisicing elit. Consequatur aspernatur totam praesentium eum eligendi dolore reiciendis doloribus vitae maiores eos?.</p>
                <p class="p4">Instructor Name</p>
                <button class="btn5" onclick="deleteCourse()">Delete</button>
                <button class="btn6" onclick="closeModal()">Back</button>
            </div>
        </div>

        <script src="courselist.js"></script>

</body>
</html>