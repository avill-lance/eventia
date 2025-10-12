<?php include __DIR__."/components/header.php"; ?>
    <div class="container d-flex vh-90 gap-4 align-items-center justify-content-center mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="section-title text-center">Profile Information</h1>
            </div>
            <form method="POST">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" value="<?php echo $rows["first_name"]; ?>" class="form-control editing" id="firstname" name="firstname" readonly>
                        </div>
                        <div class="col">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" value="<?php echo $rows["last_name"]; ?>" class="form-control editing" id="lastname" name="lastname" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="text" value="<?php echo $rows["email"]; ?>" class="form-control editing" id="email" name="email" readonly>
                        </div>
                        <div class="col">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="number" value="<?php echo $rows["phone"]; ?>" class="form-control editing" id="phone" name="phone" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="zip" class="form-label">Zip Code</label>
                            <input type="text" value="<?php echo $rows["zip"]; ?>" class="form-control editing" id="zip" name="zip" readonly>
                        </div>
                        <div class="col">
                            <label for="city" class="form-label">City</label>
                            <input type="text" value="<?php echo $rows["city"]; ?>" class="form-control editing" id="city" name="city" readonly>
                        </div>
                    </div>
                    <div class="row-12 mb-5">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" value="<?php echo $rows["address"]; ?>" class="form-control editing" id="address" name="address" readonly>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-secondary w-100" type="button" id="cancelBtn">Cancel Edit</button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success w-100" type="submit" id="passBtn" name="passBtn">Confirm Edit</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4y mt-2">
                            <button class="btn btn-info w-100" type="button" id="changePassBtn" name="changePassBtn">Change Password</button>
                        </div>
                        <div class="col-4y mt-2">
                            <a class="btn btn-warning w-100" type="button" id="viewTransactions" name="viewTransactions" href="viewtransactions.php" >Transactions</a>
                        </div>
                        <div class="col-4y mt-2">
                            <button class="btn btn-secondary w-100" type="button" id="editBtn" name="editBtn">Edit Profile</button>
                        </div>
                        <div class="col-4y mt-2">
                            <button class="btn btn-danger w-100" type="button" id="logoutBtn" name="logoutBtn">Logout</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $("#viewForm").submit(function(e){
                e.preventDefault();
                $.ajax({
                    url:'functions/ViewTransactions.php',
                    method:"POST",
                    data: $("#viewForm").serialize(),

                    success: function(phpresponse){
                        if(phpresponse.message.trim()==='verified'){
                            window.location.href='functions/ViewTransactions.php';
                            exit(0);
                        }
                        else{
                            Swal.fire({
                                title: "User is cannot be found",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#d33",
                                confirmButtonText: "okay",
                            })
                        }
                    },
                    error: function(){
                        console.error('AJAX Error: ' + error);
                        Swal.fire('Error!', 'Network error. Please check your connection.', 'error');
                    }
                })
            })
        })

    </script>

<?php include __DIR__."/components/footer.php" ?>