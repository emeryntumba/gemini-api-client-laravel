<!DOCTYPE html>
<html>
<head>
    <title>Gemini API - Génération de contenu</title>
</head>
<body>
    <h1>Génération de contenu avec Gemini</h1>

    <form action="{{ route('gemini.generate') }}" method="POST">
        @csrf
        <label for="text">Entrez du texte :</label>
        <textarea id="text" name="text" rows="4" cols="50"></textarea>
        <br>
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
