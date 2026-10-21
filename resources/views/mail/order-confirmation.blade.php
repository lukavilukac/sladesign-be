<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potvrda Narudžbine</title>
</head>
<body style="font-family: Arial, sans-serif;">

<!-- Company Logo -->
<div style="text-align: center; margin-bottom: 20px;">
    <img src="putanja/do/logo.png" alt="Logo Kompanije" style="max-width: 200px;">
</div>

<!-- Detalji Narudžbine -->
<h1 style="text-align: center; color: #333;">Potvrda Narudžbine</h1>

<p>Zdravo ,{{$user->first_name}} {{$user->last_name}}</p>

<p>Vaša narudžbina sa sledećim detaljima je potvrđena:</p>

<!-- Tabela sa Proizvodima -->
<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Proizvod</th>
        <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Količina</th>
        <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Cena</th>
    </tr>
    </thead>
    <tbody>
    <!-- Redovi sa Proizvodima -->
    @foreach($order->order_lines as $orderLine)
        <tr>
            <td style="border: 1px solid #ddd; padding: 10px;">
                <img src="{{$orderLine->product->product_image->thumbnail_path}}" alt="Slika Proizvoda" style="max-width: 80px; margin-right: 10px;">{{$orderLine->product->name}}
            </td>
            <td style="border: 1px solid #ddd; padding: 10px;">{{$orderLine->quantity}}</td>
            <td style="border: 1px solid #ddd; padding: 10px;">{{$orderLine->price}} RSD</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="2" style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>Dostava:</strong></td>
        <td style="border: 1px solid #ddd; padding: 10px;"><strong>500 RSD</strong></td>
    </tr>

    <!-- Dodajte više redova sa proizvodima po potrebi -->
    <tr>
        <td colspan="2" style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>Broj porudžbine:</strong></td>
        <td style="border: 1px solid #ddd; padding: 10px;"><strong>{{ $order->id }}</strong></td>
    </tr>
    <tr>
        <td colspan="2" style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>Datum:</strong></td>
        <td style="border: 1px solid #ddd; padding: 10px;"><strong>{{ $order->created_at->format('d. m. Y. H:i:s') }}</strong></td>
    </tr>
    <!-- Red za Ukupnu Sumu -->
    <tr>
        <td colspan="2" style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>Ukupno:</strong></td>
        <td style="border: 1px solid #ddd; padding: 10px;"><strong>{{$order->price + 500}} RSD</strong></td>
    </tr>
    </tbody>
</table>

<!-- Poruka o Zatvaranju -->
<p>Hvala vam što ste odabrali SlaDesign. Cenimo vaše poverenje!</p>

</body>
</html>
