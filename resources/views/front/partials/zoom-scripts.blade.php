<script>
    function openZoomedImage(imageSrc) {
        const zoomedImage = document.createElement("div");
        zoomedImage.classList.add("zoomed-wrapper");

        zoomedImage.innerHTML = `<div class="zoomed-container">
            <img src="${imageSrc}" class="zoomed-img">
            <span class="close-btn" onclick="closeZoom()">✖</span>
        </div>`;
        document.body.appendChild(zoomedImage);
    }
    function closeZoom() {
        const zoomedImage = document.querySelector(".zoomed-wrapper");
        if (zoomedImage) {
            zoomedImage.remove();
        }
    }
</script>
