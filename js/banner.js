const img = document.querySelector(".bannerimg");
const pagination = document.querySelector(".pagination");

const imagesArry = [
  "./logos/banner_1.png",
  "./logos/banner_2.png",
  "./logos/banner_3.png",
];

let currentImage = 0;

pagination.innerHTML = imagesArry
  .map(
    (i, index) =>
      `<button onclick="changeCurrentimage(${index})" class="dot"></button>`
  )
  .join("");

const dots = document.querySelectorAll(".dot");

dots[currentImage].classList.add("active");
img.src = imagesArry[currentImage];

function changeImage() {
  img.src = imagesArry[currentImage];
  dots.forEach((dot) => dot.classList.remove("active"));
  dots[currentImage].classList.add("active");
}

async function moveTo(num) {
  if (num == -1 && currentImage === 0) {
    currentImage = imagesArry.length - 1;
  } else if (num == 1 && currentImage === imagesArry.length - 1) {
    currentImage = 0;
  } else {
    currentImage += num;
  }

  changeImage();
}

const changeCurrentimage = (index) => {
  currentImage = index;
  changeImage();
};

setInterval(() => moveTo(1), 5000);
