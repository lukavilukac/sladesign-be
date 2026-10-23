<!DOCTYPE html>
<html lang="sr">
<body>
    <h1>Greška u aplikaciji {{ config('app.name') }}</h1>
    <p><strong>Naziv:</strong> {{ $errorTitle }}</p>
    <p><strong>Nivo:</strong> {{ $level }}</p>
    <p><strong>Okruženje:</strong> {{ config('app.env') }}</p>
    <p><strong>Vreme:</strong> {{ $occurredAt }}</p>
    <p><a href="{{ $viewerUrl }}">Pronađi grešku u Application Logs</a></p>
    <p>Potrebna je prijava administratorskim nalogom. Detalji i stack trace su dostupni u Log Viewer-u.</p>
</body>
</html>
