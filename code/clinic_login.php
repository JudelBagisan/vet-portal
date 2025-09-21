<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetPortal</title>
    <link rel="stylesheet" href="./output.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-offwhite-100">

    <div class="flex w-full h-screen flex-col md:flex-row">
        
     <div class="w-full md:w-1/2 relative md:order-2">
    <img src="./media/pagepic.png" class="w-full h-full object-cover">
    </div>
        <div class="w-full md:w-1/2 max-w-md p-8 flex flex-col justify-center mx-auto md:order-1">
            <a href="#" class="text-grey-100 text-sm mb-4">&larr; Back to Homepage</a>
            <h2 class="text-3xl font-semibold text-of   fblack-100">Login to VetPortal</h2>
            <p class="text-sm text-grey-100 mb-6">Fill the form below to Login</p>

            <form action="login_validate_clinic.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-offblack-100 mb-1">Email Address</label>
                    <input type="email" name="emailClinic" required placeholder="Enter email address" class="bg-white w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-offblack-100 mb-1">Password</label>
                    <input type="password" name="passwordClinic" required placeholder="Enter password" class="bg-white w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <a href="#" class="text-sm hover:underline text-blue-500 font-bold">Forgot Password?</a>
                </div>
                 <?php
                    if(isset($_GET['invalid'])){ ?>
                    <div class="border-red-300 border bg-red-100 p-3 text-center mt-2 text-red-400 rounded-md flex items-center justify-center">
                        <img src="./media/circle-exclamation-solid-full.svg" alt="Info" class="size-5 filter-red"><p>&nbsp;Email and Password does not match!</p>
                    </div>
                <?php } ?>
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 cursor-pointer text-white font-semibold py-2 rounded-md">Login</button>
            </form>

            <p class="mt-4 text-sm text-offblack-100">Doesn’t have an account? 
                <a href="./signup.html" class="text-blue-500 hover:underline font-bold">Create Account</a>
            </p>
        </div>

    </div>

</body>
</html>
