<?php

if(isset($_POST['upload-img'])){

    // Function to generate a unique filename
    function generateUniqueFilename($extension) {
        return uniqid() . '_' . time() . '.' . $extension;
    }

    // Function to create directory if it doesn't exist
    function createUploadDirectory($path) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    try {
        // Check if image data is received
        if (!isset($_POST['cropped_image'])) {
            throw new Exception('No image data received');
        }

        // Get the base64 string
        $imageData = $_POST['cropped_image'];

        // Extract the base64 encoded binary data
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        
        // Decode base64 data
        $decodedImage = base64_decode($imageData);
        
        if ($decodedImage === false) {
            throw new Exception('Error decoding image data');
        }

        // Set upload directory
        $uploadDir = 'uploads/';
        createUploadDirectory($uploadDir);

        // Generate unique filename
        $filename = generateUniqueFilename('jpg');
        $filePath = $uploadDir . $filename;

        // Save the image
        if (file_put_contents($filePath, $decodedImage) === false) {
            throw new Exception('Error saving image file');
        }

        // Redirect back with success message
        header('Location: index.php?success=1');
        exit;

    } catch (Exception $e) {
        // Redirect back with error message
        header('Location: index.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Image Cropper with Form Upload</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />
    <style>
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .image-cropper {
            margin-bottom: 20px;
        }
        .controls {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        #upload-input {
            display: none;
        }
        .preview {
            margin-top: 20px;
            text-align: center;
        }
        #cropped-data {
            display: none;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Image Cropper</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="message success">Image uploaded successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="message error">Error uploading image: <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="image-cropper"></div>
        <div class="controls">
            <input type="file" id="upload-input" accept="image/*">
            <button type="button" class="btn upload-btn">Select Image</button>
            <button type="button" class="btn crop-btn">Preview Crop</button>
        </div>
        
        <form id="upload-form" method="POST">
            <input type="hidden" name="cropped_image" id="cropped-data">
            <button type="submit" name="upload-img" class="btn save-btn">Upload to Server</button>
        </form>

        <div class="preview">
            <h3>Preview:</h3>
            <img id="preview-img" src="/api/placeholder/400/300" alt="Preview" style="display: none;">
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var cropInstance = $('.image-cropper').croppie({
                viewport: {
                    width: 300,
                    height: 300,
                    type: 'square' //square, rectangle, circle
                },
                boundary: {
                    width: 400,
                    height: 400
                }
            });

            $('.upload-btn').on('click', function() {
                $('#upload-input').click();
            });

            $('#upload-input').on('change', function(e) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    cropInstance.croppie('bind', {
                        url: event.target.result
                    });
                }
                reader.readAsDataURL(e.target.files[0]);
            });

            $('.crop-btn').on('click', function() {
                cropInstance.croppie('result', {
                    type: 'canvas',
                    size: 'viewport',
                    format: 'jpeg'
                }).then(function(result) {
                    $('#preview-img').attr('src', result);
                    $('#preview-img').show();
                    $('#cropped-data').val(result);
                    $('.save-btn').show();
                });
            });
        });
    </script>
</body>
</html>