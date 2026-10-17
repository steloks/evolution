<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Clean Air Finder</title>
        @vite(['resources/js/app.js'])
    </head>
    <body>
        <div id="app" data-first-day="{{ $firstDay }}" data-last-day="{{ $lastDay }}"></div>
    </body>
</html>
