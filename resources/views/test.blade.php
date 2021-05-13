<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <form action="/test" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
            <input type="file" name="image" id="image">
            <button type="submit">Aceptar</button>
        </form>
    <body>
</html>