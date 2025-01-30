import { NextResponse } from 'next/server';

export async function POST(request) {
  const apiUrl = 'http://localhost:8000/api/ApiScraper.php';

  try {
    const requestData = await request.json();
    console.log("Request Data:", requestData);

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(requestData),
    });

    if (response.ok && response.headers.get('Content-Type') === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
      const blob = await response.blob();
      return new NextResponse(blob, {
        headers: {
          'Content-Type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'Content-Disposition': 'attachment; filename="resultado.xlsx"',
        },
      });
    }

    // Handle the normal response when it's not a file
    const rawData = await response.text();
    const sanitized = '[' + rawData.replace(/}{/g, '},{') + ']';
    const res = JSON.parse(sanitized);

    return NextResponse.json(res);

  } catch (error) {
    console.error("Error during request:", error.message);
    return NextResponse.json({ error: 'Internal Server Error', message: error.message }, { status: 500 });
  }
}