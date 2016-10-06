Closify v1.0.5
=======

Here is the Closify official page [Demo](http://www.itechflare.com/closify "Closify page").
----------------------------

![sample](https://github.com/aelbuni/closify/blob/master/closify.jpg)

Client Side
-----------
Closify is a jQuery based plugin, that simplifies the developers task to create a dynamic image uploader. With closify you can easily create any kind of image convas (cover, profile, or custom banner) with whatever size you define, and intelligently enough the Closify plugin would resize the image according to the size of container you defined with a respect of the image aspect ratio, and then it start to generate a dynamic widget that give you the capability to position/reposition your photo adequately; save the photo with the desired position and submit the position information to the server side for storage; change the photo you have chosen and just delete the selected photo.

<div id="profile-exp2" class="closify" height="100" width="400"></div>
<div id="tiny-exp2" class="closify" height="150" width="220"></div>

Make sure to put a unique ID for every DIV that need to be Closified, and also you have to make sure that you have included the desired width and height as shown in the above example.

Now all you have to do is to add a line of javascript code for every DIV that need to be closifed as follow:

```
$(document).ready(function(){
         
        // Closify a div with default options
        $("#tiny-img").closify();
         
        // Closify a div with options
        $("#profile-img").closify(
            {
                allowedFileSize: 1024 * 1024 * 10, // (10 MB) Maximum image size limit
                url: "processupload.php",           // URL on where the photo should be submitted
                dataType: "json",                   // The result data type that should be anticipated for the upload request result
                targetOutput: "#output-profile-img",// Where to render errors and notification messages
                type: "post",                       // Type of post
                error: anyFunctionName,             // Event handler for upload error
                success: anyFunctionName,           // Event handler for successful upload
                uploadProgress: anyFunctionName,    // Event handler for upload progress (In Percentage)
                beforeSubmit:  anyFunctionName      // Before submission event handler
            });
    }
);
```

As you can see you can either Closify a DIV with default options as for the #tiny-img case, or overriding the options as shown for the #profile-img case.

The plugin default options are as follow:

```
allowedFileSize: 1024 * 1024 * 10, // (10 MB) Maximum image size limit
url: "processupload.php",           // URL for where the photo should be submitted, and by default the processupload file should be put inside the root folder.
dataType: "json",                   // The result data type that should be anticipated for the upload request result
targetOutput: "#output-"+<div-id>,// Where to render errors and notification messages
type: "post",                       // Type of post
beforeSubmit:  beforeSubmit         // Notice: By the default the plugin does validate the file size, type and validity, and if you override this event; then, you have to handle the validation process on your own.
```                

Server side
-----------
From the server side there will be only one PHP file that should process the images that have been uploaded from the client side, and this single processing file is represemted by the "processupload.php" file. This file is responsible of generating proper photos/thumbnails; where the resized photos are sent back to the client side using AJAX to be shown to the user seamlessly.

Here are the options that you can configure yourself to change the behaviour of the processing:

```
############ Edit settings ##############
$imageName              = $_POST["ImageName"];
$ThumbSquareSize        = 200; //Thumbnail will be 200x200
$container_width        = $_POST["w"];
$container_height       = $_POST["h"];
$ThumbPrefix            = "thumb_"; //Normal thumb Prefix
$DestinationDirectory   = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR; //specify upload directory ends with / (slash)
$Quality                = 90; //jpeg quality
##########################################
```

However, it is your task to store the image url generated from this code into database so it can be rendered later according to the UserID, where the UserID will point the user who uploaded the pictures. And also there is another place where you can retrieve the image offset from users so it can be save or stored for later usage. The "processupload.php" is considered to be highly documented and easy to read, and you will have hints inside to where you supposed to put your processing code for the above mentioned reasons as follow:

```
1.
    /****************************************************/
    /****************************************************/
    /*
    // Insert info into database table!
    mysql_query("INSERT INTO myImageTable (ImageName, ThumbName, ImgPath)
    VALUES ($DestRandImageName, $thumb_DestRandImageName, 'uploads/')");
    /****************************************************/
    /****************************************************/
2. 
    // When somebody saves a picture you can read offsetY and offsetX and save them, so it become stored
    // offsetX / offsetY
```
