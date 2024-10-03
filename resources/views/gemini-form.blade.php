<!DOCTYPE html>
<html>
<head>
    <title>Gemini API - Génération de contenu</title>
</head>
<body>
    <h1>Génération de contenu avec Gemini</h1>

    <form action="{{ route('gemini.generate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="text">Entrez du texte :</label>
        <textarea id="text" name="text" rows="4" cols="50"></textarea>
        <br><br>

        <label for="image">Téléchargez une image :</label>
        <input type="file" id="image" name="image" accept="image/*">
        <br><br>

        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
