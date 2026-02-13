<style>
.image-container {
    position: relative;
    display: inline-block;
}

.thumbnail {
    width: 200px; /* Taille normale */
    cursor: pointer;
}

.zoom-icon {
    position: absolute;
    top: 10px;
    right: -10px;
    font-size: 18px;
    background: rgba(160, 160, 160, 0.7);
    color: white;
    padding: 5px;
    border-radius: 50%;
    cursor: pointer;
    display: none;
}

.image-container:hover .zoom-icon {
    display: block;
}

.zoomed-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.zoomed-container {
    position: relative;
}

.zoomed-img {
    max-width: 90%;
    max-height: 90%;
    box-shadow: 0px 0px 10px #fff;
    box-shadow: 0px 0px 5px 10px rgba(250, 250, 250, 0.8), 0px 0px 10px 15px rgba(50, 50, 250, 0.5);
}

.close-btn {
    position: absolute;
    top: -40px;
    right: 0;
    font-size: 25px;
    background: rgba(160, 160, 160, 0.7);
    color: white;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 50%;
}

</style>
