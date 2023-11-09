<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo isset($title) ? $title : "Home - Demo Back-End 1" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>

    <?php
    include_once HEADER;
    include_once $path;
    include_once FOOTER;
    ?>
    <!-- <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <script src="<?php //echo BASE_URL ?>/public/ckfinder/ckfinder.js"></script>

    <script>
        var editor = CKEDITOR.replace('editor1');
        CKFinder.setupCKEditor(editor);
    </script> -->
</body>

</html>