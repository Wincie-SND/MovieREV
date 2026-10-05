<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MovieREV</title>

    @vite(['resources/css/app.css'])
</head>

<body>

    <div class="page-title">
        Home Page (Signed out)
    </div>

    <div class="movie-app">

        <header class="header">

            <div class="brand">
                <div class="film-logo">🎞️</div>
                <div class="brand-name">MovieREV</div>
            </div>

            <div class="header-right">
                <a href="#" class="signin">Sign In</a>
                <a href="#" class="header-button">Get Started!</a>
            </div>

        </header>

        <div class="main-container">

            <aside class="sidebar">

                <nav class="sidebar-nav">

                    <a href="#" class="nav-item active">
                        <span>⌂</span>
                        <span>Home</span>
                    </a>

                    <a href="#" class="nav-item">
                        <span>▣</span>
                        <span>Movies</span>
                    </a>

                    <a href="#" class="nav-item">
                        <span>⚑</span>
                        <span>Lists</span>
                    </a>

                    <a href="#" class="nav-item">
                        <span>★</span>
                        <span>Reviews</span>
                    </a>

                </nav>

            </aside>

            <main class="content">

                <section class="welcome">
                    <h1>Welcome!</h1>

                    <a href="#" class="get-started">
                        Get Started
                    </a>
                </section>

                <div class="search-container">
                    <input type="text" placeholder="Find a movie...">
                    <button type="button">🔍</button>
                </div>

                <section class="trending">

                    <div class="section-title">
                        Trending
                    </div>

                    <div class="section-line"></div>

                    <div class="movie-list">

                        <a href="#" class="movie-card">
                            <img src="/images/resident-evil.jpg"
                                 alt="Resident Evil">
                        </a>

                        <a href="#" class="movie-card">
                            <img src="/images/spider-man.jpg"
                                 alt="Spider-Man">
                        </a>

                        <a href="#" class="movie-card">
                            <img src="/images/obsession.jpg"
                                 alt="Obsession">
                        </a>

                        <a href="#" class="movie-card">
                            <img src="/images/odyssey.jpg"
                                 alt="The Odyssey">
                        </a>

                        <a href="#" class="movie-card">
                            <img src="/images/couple.jpg"
                                 alt="Couple">
                        </a>

                        <a href="#" class="movie-card">
                            <img src="/images/end-dark-street.jpg"
                                 alt="The End of the Dark Street">
                        </a>

                    </div>

                </section>

            </main>

        </div>

    </div>

</body>
</html>