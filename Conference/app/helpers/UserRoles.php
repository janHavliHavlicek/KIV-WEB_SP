<?php
if (isset($_SESSION["logged_user"])) {
    $loggedUser = $_SESSION["logged_user"]["username"];

    if($_SESSION["logged_user"]["status"] == "author")
    {
        $authorVisible = true;
        $administratorVisible = $reviewerVisible = false;
    }
    else if($_SESSION["logged_user"]["status"] == "reviewer")
    {
        $reviewerVisible = true;
        $administratorVisible = $authorVisible = false;
    }
    else if($_SESSION["logged_user"]["status"] == "administrator")
    {
        $administratorVisible = true;
        $reviewerVisible = $authorVisible = false;
    }
} else {
    $loggedUser = " log in...";
    $authorVisible = $reviewerVisible = $administratorVisible = false;
}
?>