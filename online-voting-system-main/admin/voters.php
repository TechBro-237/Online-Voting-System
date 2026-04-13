<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Manage Voters
        </h1>
      </section>
      <!-- Main content -->
      <section class="content">
        <?php
          if(isset($_SESSION['error'])){
            echo "
              <div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                <h4><i class='icon fa fa-warning'></i> Error!</h4>
                ".$_SESSION['error']."
              </div>
            ";
            unset($_SESSION['error']);
          }
          if(isset($_SESSION['success'])){
            echo "
              <div class='alert alert-success alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                <h4><i class='icon fa fa-check'></i> Success!</h4>
                ".$_SESSION['success']."
              </div>
            ";
            unset($_SESSION['success']);
          }
        ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
              </div>
              <div class="box-body">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Voters ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Profile</th>
                    <th>Actions</th>
                  </thead>
                  <tbody>
                    <?php
                      $sql = "SELECT * FROM voters";
                      $query = $conn->query($sql);
                      while($row = $query->fetch_assoc()){
                        $image = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                        echo "
                          <tr>
                            <td>".$row['voters_id']."</td>
                            <td>".$row['firstname']."</td>
                            <td>".$row['lastname']."</td>
                            <td>
                              <img src='".$image."' width='30px' height='30px'>
                              <a href='#edit_photo' data-toggle='modal' class='pull-right photo' data-id='".$row['id']."'><span class='fa fa-edit'></span></a>
                            </td>
                            <td>
                              
                                <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Edit</button>
                                <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete</button>
                                <button class='btn btn-info btn-sm print btn-flat' data-id='".$row['id']."'><i class='fa fa-print'></i> Print</button>
                              

                            </td>
                          </tr>
                        ";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>   
    </div>
    <?php include 'includes/voters_modal.php'; ?>
  </div>
  <?php include 'includes/scripts.php'; ?>

  <script>
  $(function(){
    $(document).on('click', '.edit', function(e){
      e.preventDefault();
      $('#edit').modal('show');
      var id = $(this).data('id');
      getRow(id);
    });

    $(document).on('click', '.delete', function(e){
      e.preventDefault();
      $('#delete').modal('show');
      var id = $(this).data('id');
      getRow(id);
    });

    $(document).on('click', '.photo', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      getRow(id);
    });

  });

  function getRow(id){
    $.ajax({
      type: 'POST',
      url: 'voters_row.php',
      data: {id:id},
      dataType: 'json',
      success: function(response){
        $('.id').val(response.id);
        $('#edit_firstname').val(response.firstname);
        $('#edit_lastname').val(response.lastname);
        $('#edit_password').val(response.password);
        $('.fullname').html(response.firstname+' '+response.lastname);
      }
    });
  }
  $(document).on('click', '.print', function(e){
  e.preventDefault();
  var id = $(this).data('id');

  $.ajax({
    type: 'POST',
    url: 'voters_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // ID card layout with coat of arms background and seal
      var printContent = `
        <div style="
          width:350px; 
          height:255px; 
          border:2px solid #000; 
          padding:15px; 
          font-family:Arial, sans-serif; 
          position:relative; 
          background-image: url('../images/CoatOfArms.png'); 
          background-size: cover; 
          background-position: center; 
          background-repeat: no-repeat;
        ">
          
          <!-- Semi-transparent overlay for readability -->
          <div style="
            width:100%; 
            height:100%; 
            background-color: rgba(255,255,255,0.85); 
            padding:10px; 
            box-sizing:border-box;
            position:relative;
          ">
            
            <!-- Election Header -->
            <div style="text-align:center; margin-bottom:10px;">
              <img src='../images/flag.png' style='width:40px; vertical-align:middle;'>
              <span style="font-size:16px; font-weight:bold; margin-left:10px;">
                Cameroon Presidential Election 2025
              </span>
            </div>
            
            <!-- Voter Info -->
            <div style="display:flex; align-items:flex-start; margin-top:10px; position:relative;">
              <div style="flex:1;">
                <p><strong>Voter ID:</strong> ${response.voters_id}</p>
                <p><strong>Name:</strong> ${response.firstname} ${response.lastname}</p>
              </div>
              <div style="position:relative;">
                <img src="../images/${response.photo ? response.photo : 'profile.jpg'}" width="80" height="80" style="border:1px solid #000;">
                
                <!-- Seal positioned below profile picture -->
                <img src="../images/seal.jpg" width="60" height="60" 
                     style="position:absolute; top:100px; left:10px; opacity:0.9;">
              </div>
            </div>
            
            <!-- Footer -->
            <div style="position:absolute; bottom:10px; width:90%; text-align:center; font-size:12px; color:#555; padding-bottom:5px;">
              Official Voter's Card - Government of Cameroon
            </div>

          </div>
        </div>
      `;

      var w = window.open('', '', 'width=400,height=300');
      w.document.write(printContent);
      w.document.close();
      w.print();
    }
  });
});



  </script>
</body>
</html>
