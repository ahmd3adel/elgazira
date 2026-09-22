<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <title>محضر فحص</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            direction: rtl;
            color: #000;
            background: #fff;
        }

        .print-container {
            width: 100%;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            align-items: start;
            margin-bottom: 10px;
        }

        .header-right {
            text-align: right;
            line-height: 1.8;
        }

        .header-center {
            text-align: center;
            font-weight: bold;
        }

        .header-center h2 {
            margin: 0;
            font-size: 18px;
            text-decoration: underline;
        }

        .header-center div {
            margin-top: 6px;
        }

        .header-left {
            text-align: left;
            line-height: 1.8;
        }

        .instructions {
            border: 1px solid #ccc;
            background: #f7f7f7;
            padding: 10px;
            margin-bottom: 8px;
            min-height: 55px;
        }

        .instructions-title {
            font-weight: bold;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #f1f1f1;
            font-weight: bold;
        }

        .info-table {
            margin-bottom: 8px;
        }

        .info-table td {
            height: 25px;
        }

        .inspection-table th {
            height: 25px;
        }

        .inspection-table td {
            height: 25px;
        }

        .statement {
            text-align: right;
            padding-right: 8px;
        }

        .check {
            font-size: 16px;
            color: green;
            font-weight: bold;
        }

        .blank {
            display: inline-block;
            min-width: 100px;
            border-bottom: 1px solid #000;
        }

        .products-table {
            margin-top: 8px;
        }

        .products-table td,
        .products-table th {
            height: 25px;
        }

        .notes {
            margin-top: 8px;
            border: 1px dashed #000;
            height: 55px;
            padding: 7px;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 12px;
            text-align: center;
        }

        .signature {
            padding-top: 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .print-button {
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 10px 18px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>

<button class="print-button" onclick="window.print()">
    🖨 طباعة
</button>

<div class="print-container">

    {{-- Header --}}
    <div class="header">

        <div class="header-right">
            <div>
                <strong>الجمعية التعاونية لأهالي حي الجزيرة</strong>
            </div>

            <div>
                مخزن رئيسي للمنزل
            </div>
        </div>

        <div class="header-center">
            <h2>محضر فحص</h2>
            <div>(نموذج SF18)</div>
        </div>

        <div class="header-left">
            <div>
                رقم المستند:
                <span class="blank"></span>
            </div>

            <div>
                التاريخ:
                <span class="blank"></span>
            </div>
        </div>

    </div>


    {{-- تعليمات الفحص --}}
    <div class="instructions">

        <div class="instructions-title">
            تعليمات الفحص:
        </div>

        يتم فحص نسبة 10% من الكمية الموردة من كل صنف
        ويمكن زيادة نسبة الفحص لتصل إلى 100% عند الحاجة.

    </div>


    {{-- بيانات الشحنة --}}
    <table class="info-table">

        <tr>

            <th>رقم العربية</th>
            <th>رقم الطبلة</th>
            <th>اسم السائق</th>
            <th>رقم إذن المورد/الصرف/الارتجاع</th>
            <th>تاريخ الاستلام/الصرف/الارتجاع</th>
            <th>اسم المورد</th>

        </tr>

        <tr>

            <td>
                <span class="blank"></span>
            </td>

            <td>
                <span class="blank"></span>
            </td>

            <td>
                <span class="blank"></span>
            </td>

            <td>
                {{ $order->document_number }}
            </td>

            <td>
                {{ $order->arrival_time
                    ? \Carbon\Carbon::parse($order->arrival_time)->format('Y-m-d')
                    : '-' }}
            </td>

            <td>
                {{ $order->supplier_name ?? '-' }}
            </td>

        </tr>

    </table>


    {{-- بنود الفحص --}}
    <table class="inspection-table">

        <thead>

            <tr>

                <th style="width: 28%;">
                    البيان
                </th>

                <th style="width: 10%;">
                    الحالة
                </th>

                <th style="width: 30%;">
                    الملاحظات
                </th>

            </tr>

        </thead>

        <tbody>

            @php

                $questions = [

                    'هل درجة حرارة الكراتين داخل السيارة مطابقة لدرجة حرارة المنتج المخزنة؟',

                    'هل العبوات نظيفة من الخارج؟',

                    'هل العبوات أو الكراتين سليمة؟',

                    'هل توجد أي علامات تلف أو تغير في المنتج؟',

                    'هل توجد أي علامات تسرب من العبوات؟',

                    'هل يوجد أي أثر صدأ أو تغير داخل صندوق السيارة؟',

                    'هل الشكل الخارجي للكراتين جيد ولا يوجد بها أي عيوب من الخارج؟',

                    'هل عدد بوالص سكوب في كل كرتونة يساوي 120 بلك؟',

                    'هل العبوات سليمة من الداخل وخالية من أي تلوث؟',

                    'هل العبوة محكمة الغلق ولا يوجد بها أي تسريب؟',

                    'هل المنتج داخل الكرتونة بالكامل سليم؟',

                    'هل يوجد باركود على الكرتونة من الخارج؟',

                    'هل عدد الكراتين التي تم فحصها مطابق؟',

                    'هل تاريخ الإنتاج والانتهاء مطبوع بشكل واضح على الكرتونة من الخارج وعلى البيانات؟',

                    'هل العبوة مناسبة ولا يوجد بها أي عيوب أثناء الفحص؟',

                ];

            @endphp

            @foreach($questions as $index => $question)

                <tr>

                    <td class="statement">
                        {{ $index + 1 }}.
                        {{ $question }}
                    </td>

                    <td>
                        <span class="check">☑</span>
                    </td>

                    <td>
                        ______________________________
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    {{-- بيانات الأصناف --}}
    <table class="products-table">

        <thead>

            <tr>

                <th>م</th>
                <th>اسم الصنف</th>
                <th>الوحدة</th>
                <th>رقم التشغيلة</th>
                <th>تاريخ الإنتاج</th>
                <th>تاريخ الانتهاء</th>
                <th>الكمية</th>
                <th>ملاحظات</th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>1</td>

                <td>
                    {{ $order->product_name ?? '-' }}
                </td>

                <td>
                    -
                </td>

                <td>
                    {{ $order->batch_number ?? '-' }}
                </td>

                <td>
                    {{ $order->production_date
                        ? \Carbon\Carbon::parse($order->production_date)->format('Y-m-d')
                        : '-' }}
                </td>

                <td>
                    -
                </td>

                <td>
                    {{ $order->quantity }}
                </td>

                <td>
                    {{ $order->notes ?? '' }}
                </td>

            </tr>

        </tbody>

    </table>


    {{-- الملاحظات --}}
    <div class="notes">

        <strong>ملاحظات:</strong>

        {{ $order->notes ?? '' }}

    </div>


    {{-- التوقيعات --}}
    <div class="signatures">

        <div class="signature">

            <div class="signature-line"></div>

            <strong>مراقب جودة المخزن</strong>

            <br>

            التوقيع: __________________

        </div>


        <div class="signature">

            <div class="signature-line"></div>

            <strong>أمين عهدة المخزن</strong>

            <br>

            التوقيع: __________________

        </div>


        <div class="signature">

            <div class="signature-line"></div>

            <strong>المسؤول عن المخزن</strong>

            <br>

            التوقيع: __________________

        </div>

    </div>

</div>

</body>

</html>