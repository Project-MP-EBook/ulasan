<?php 
    session_start();
    include("koneksi.php");
    if(isset($_SESSION['user_id'])){
        $id = $_SESSION['user_id'];
        $query = mysqli_query($konek,"select * from users where role='user' and user_id='$id'")or die (mysqli_error($konek));
        while($data=mysqli_fetch_array($query)){
            $_SESSION['email'] = $data['email'];
            $_SESSION['name'] = $data['name'];
            $_SESSION['fullname'] = $data['fullname'];
            $_SESSION['major'] = $data['major'];
            $_SESSION['university'] = $data['university'];
            $_SESSION['profile_picture'] = $data['profile_picture'];
        }
    }else{
        header("Location: user/login.php");
    }
    
    if(isset($_GET['id'])){
        $id = intval($_GET['id']);
        
        // Query untuk mengambil data buku
        $query_books = mysqli_query($konek, "SELECT title, description, cover, price FROM books WHERE book_id = $id") or die(mysqli_error($konek));
        
        if(mysqli_num_rows($query_books) > 0) {
            $book = mysqli_fetch_assoc($query_books);
        } else {
            echo "Data tidak ditemukan.";
            exit;
        }
    
        // Query untuk menghitung jumlah ulasan dan rata-rata rating
        $query_rating_summary = mysqli_query($konek, "SELECT COUNT(*) AS total_reviews, AVG(rating) AS average_rating FROM rating WHERE book_id = $id") or die(mysqli_error($konek));
        $rating_summary = mysqli_fetch_assoc($query_rating_summary);

        // Query untuk mengambil ulasan dari tabel rating
        $query_reviews = mysqli_query($konek, "SELECT user_id, rating, review FROM rating WHERE book_id = $id") or die(mysqli_error($konek));
        $reviews = mysqli_fetch_all($query_reviews, MYSQLI_ASSOC); // Mengambil semua ulasan
    } else {
        echo "ID buku tidak diberikan.";
        exit;
    }

    if(isset($_POST['rating'], $_POST['review'], $_GET['id'], $_POST['id_review'])) {
        $id_review = $_POST['id_review'];
        $user_id = $_SESSION['user_id'];
        $book_id = intval($_GET['id']);
        $rating = intval($_POST['rating']);
        $review = mysqli_real_escape_string($konek, trim($_POST['review']));
    
        $query = "UPDATE rating SET rating = '$rating', review = '$review' WHERE rating_id = '$id_review'";
    
        if (mysqli_query($konek, $query)) {
            header("Location: book_detail.php?id=$book_id");
            exit;
        } else {
            echo "Terjadi kesalahan: " . mysqli_error($konek);
        }
        } else if(isset($_POST['rating'], $_POST['review'], $_GET['id'])) {
            $user_id = $_SESSION['user_id'];
            $book_id = intval($_GET['id']);
            $rating = intval($_POST['rating']);
            $review = mysqli_real_escape_string($konek, trim($_POST['review']));
        
            $query = "INSERT INTO rating (user_id, book_id, rating, review) VALUES ('$user_id', '$book_id', '$rating', '$review')";
        
            if (mysqli_query($konek, $query)) {
                header("Location: book_detail.php?id=$book_id");
                exit;
            } else {
                echo "Terjadi kesalahan: " . mysqli_error($konek);
            }
            } else {
                echo "Data tidak valid.";
        } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <title>Profile Pengguna</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background-color: #F9F9F9;
        }
        .navbar{
            background-color: #1E5B86;
        }
        .nav-link{
            color:white;
        }
        .profile-nav{
            border-radius: 20%;
            width: 50px;
            height: 38px;
        }
        p{
            color:white;
        }
        .img-top img{
            width: 100%;
            margin-bottom: 10px;
        }
        main{
            width: 90%;
        }
        .btn{
            background-color: #1E5B86;
        }
        .profile-img{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .name-email{
            margin-left: 20px;
        }
        .d-flex .profile-pict{
            width: 80px;
            height: 80px;
            border-radius: 100%;
        }
        .name-email{
            margin-top: 14px;
        }
        .form-section {
            display: flex;
            gap: 20px;
        }
        .form-section .left, .form-section .right {
            flex: 1;
        }
        .form-label {
            color: #555;
        }
        img.img-fluid {
        max-width: 100%;
        height: auto;
    }

    h1 {
        font-size: 1.8rem;
        font-weight: 600;
    }

    h3 {
        font-size: 1.6rem;
        font-weight: 700;
    }

    p {
        font-size: 1rem;
        line-height: 1.6;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    button.btn {
        padding: 10px 20px;
        border-radius: 5px;
    }

    button.btn-outline-primary {
        border: 1px solid #1E5B86;
        color: #1E5B86;
        transition: 0.3s ease-in-out;
    }
    button.btn-outline-primary:hover {
        background-color: #1E5B86;
        color: white;
    }
    .rating .star {
        font-size: 2rem;
        color: #ccc;
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .rating .star.selected,
    .rating .star:hover,
    .rating .star ~ .star:hover {
        color: #FFD700;
    }

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-primary px-3">
    <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
            <a class="nav-link" aria-current="page" href="index.php">Home</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" aria-current="page" href="search.php">Buy</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" aria-current="page" href="uploads/uploaded.php">Sell</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" aria-current="page" href="monetisasi.php">History</a>
            </li>
        </ul>
        <form class="d-flex" role="search" action="search.php" method="get">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
            <?php if($_SESSION['profile_picture'] != "") { ?>
                <div class="dropdown" style="width : 38px;">
                    <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0;">
                        <img class="profile-nav" src="uploads/<?=$_SESSION['profile_picture']?>" alt="Profile Picture" style="width: 38px;">
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="user/logout.php">Logout</a></li>
                    </ul>
                </div>
                <?php } else { ?>
                <div class="dropdown" style="width : 38px;">
                    <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0;">
                        <img class="profile-nav" src="default.png" alt="Profile Picture" style="width: 38px;">
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="user/logout.php">Logout</a></li>
                    </ul>
                </div>
                <?php } ?>
            <a href="notifikasi.php" style="margin-left: 8px;"><img src="notif.png" alt="Notifikasi"></a>
            <a href="cart.php" style="margin-left: 8px; padding:1px; background-color:white; border-radius:8px;"><img src="cart (2).png" alt="Cart" style="height:36px"></a>
        </form>
        </div>
    </div>
    </nav>

    <main class="m-auto py-5">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <img src="uploads/<?= htmlspecialchars($book['cover']); ?>" alt="Book Cover" class="img-fluid rounded shadow-sm" style="height: 90vh;">
            </div>
            <div class="col-lg-7 col-md-6">
                <h1 class="mb-3"><?= htmlspecialchars($book['title']); ?></h1>
                <p class="text-muted">70+ Terjual • ⭐ <?= number_format($rating_summary['average_rating'], 1); ?>/5 (<?= $rating_summary['total_reviews']; ?> ulasan)</p>
                <h3 class="text mb-4">Rp <?= number_format($book['price'], 2, ',', '.'); ?></h3>

                <?php 
                    if(isset($_GET['id_review'])){
                        $id_review = $_GET['id_review'];
                        $book_id = $_GET['id'];
                        $query = mysqli_query($konek,"select * from rating where rating_id='$id_review'")or die (mysqli_error($konek));
                        while($data=mysqli_fetch_array($query)){
                           $rating = $data['rating'];
                           $review = $data['review'];
                        } ?>
                        <!-- Form untuk memberikan ulasan -->
                        <form method="post" action="" id="reviewForm">
                            <h4>Rating</h4>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i; ?>">&#9733;</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating" value="0">
                            <input type="hidden" name="id_review" id="id_review" value="<?=$id_review;?>">
                            <div class="form-group mt-3">
                                <label for="review">Ulasan</label>
                                <textarea class="form-control" name="review" id="review" rows="5" maxlength="200" required><?=$review;?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Submit</button>
                        </form>
                       <?php } else {?>
                        <!-- Form untuk memberikan ulasan -->
                        <form method="post" action="" id="reviewForm">
                            <h4>Rating</h4>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i; ?>">&#9733;</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating" value="0">
                            <div class="form-group mt-3">
                                <label for="review">Ulasan</label>
                                <textarea class="form-control" name="review" id="review" rows="5" maxlength="200" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Submit</button>
                        </form>
                        <?php }?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const stars = document.querySelectorAll(".rating .star");
            const ratingInput = document.getElementById("rating");

            stars.forEach(star => {
                star.addEventListener("click", function () {
                    ratingInput.value = this.getAttribute("data-value");
                    stars.forEach(s => s.classList.remove("selected"));
                    this.classList.add("selected");
                    let prev = this.previousElementSibling;
                    while (prev) {
                        prev.classList.add("selected");
                        prev = prev.previousElementSibling;
                    }
                });
            });
        });
    </script>

</body>
</html>