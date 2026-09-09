/* =====================================================
   NORTH VAULT
   MAIN JAVASCRIPT
===================================================== */


/* =====================================================
   DELIVERY FEE
===================================================== */

const DELIVERY_FEE = 1500;


/* =====================================================
   DEFAULT PRODUCTS
   TEMPORARY FALLBACK ONLY
===================================================== */

const defaultProducts = [

    {
        id: 1,
        name: "HP EliteBook",
        price: 450000,
        category: "Laptop",
        image: "images/laptop.webp",
        description: "Professional HP EliteBook laptop suitable for work, school and business."
    },

    {
        id: 2,
        name: "Samsung Galaxy Phone",
        price: 350000,
        category: "Phone",
        icon: "📱",
        description: "Modern Samsung smartphone with excellent performance and display."
    },

    {
        id: 3,
        name: "Galaxy Watch",
        price: 125000,
        category: "Watch",
        icon: "⌚",
        description: "Smart watch for fitness, notifications and everyday use."
    },

    {
        id: 4,
        name: "Wireless Headphones",
        price: 75000,
        category: "Accessory",
        icon: "🎧",
        description: "Comfortable wireless headphones with quality sound."
    },

    {
        id: 5,
        name: "iPhone",
        price: 850000,
        category: "Phone",
        icon: "📱",
        description: "Premium smartphone with powerful performance."
    },

    {
        id: 6,
        name: "Gaming Laptop",
        price: 950000,
        category: "Laptop",
        icon: "🎮",
        description: "Powerful gaming laptop for gaming and demanding applications."
    },

    {
        id: 7,
        name: "Bluetooth Speaker",
        price: 55000,
        category: "Accessory",
        icon: "🔊",
        description: "Portable Bluetooth speaker with powerful sound."
    },

    {
        id: 8,
        name: "Smart Watch Pro",
        price: 180000,
        category: "Watch",
        icon: "⌚",
        description: "Advanced smartwatch with health and fitness features."
    }

];


/* =====================================================
   MYSQL PRODUCTS
===================================================== */

let northVaultProducts = [];


async function loadProductsFromDatabase() {

    try {

        const response =
            await fetch("product.php");


        if (!response.ok) {

            throw new Error(
                "Could not connect to the product database."
            );

        }


        const products =
            await response.json();


        if (!Array.isArray(products)) {

            throw new Error(
                "Invalid product data received from server."
            );

        }


        northVaultProducts =
            products;


        console.log(
            "Products loaded from MySQL:",
            northVaultProducts
        );


        displayFeaturedProducts();

        displayShopProducts();

        displayProductDetails();

        displayAdmin();


    } catch (error) {

        console.error(
            "Product loading error:",
            error
        );


        /*
         * Temporary fallback.
         * If MySQL/API is unavailable,
         * display the old default products.
         */

        northVaultProducts =
            defaultProducts;


        displayFeaturedProducts();

        displayShopProducts();

        displayProductDetails();

        displayAdmin();

    }

}


function getProducts() {

    return northVaultProducts;

}


/* =====================================================
   CART
===================================================== */

function getCart() {

    const cart =
        localStorage.getItem("northVaultCart");


    if (!cart) {

        return [];

    }


    try {

        return JSON.parse(cart);

    } catch (error) {

        console.error(
            "Cart storage error:",
            error
        );

        return [];

    }

}


function saveCart(cart) {

    localStorage.setItem(
        "northVaultCart",
        JSON.stringify(cart)
    );


    updateCartCount();

}


/* =====================================================
   ADD TO CART
===================================================== */

function addToCart(id) {

    const products =
        getProducts();


    const product =
        products.find(
            item =>
                item.id === Number(id)
        );


    if (!product) {

        alert(
            "Product could not be found."
        );

        return;

    }


    const cart =
        getCart();


    const existing =
        cart.find(
            item =>
                item.id === product.id
        );


    if (existing) {

        existing.quantity++;

    } else {

        cart.push({

            ...product,

            quantity: 1

        });

    }


    saveCart(cart);


    alert(
        product.name +
        " added to cart."
    );

}


/* =====================================================
   REMOVE FROM CART
===================================================== */

function removeFromCart(id) {

    let cart =
        getCart();


    cart =
        cart.filter(
            item =>
                item.id !== Number(id)
        );


    saveCart(cart);


    displayCart();

    displayCheckout();

}


/* =====================================================
   CHANGE CART QUANTITY
===================================================== */

function changeQuantity(id, change) {

    const cart =
        getCart();


    const item =
        cart.find(
            product =>
                product.id === Number(id)
        );


    if (!item) {

        return;

    }


    item.quantity += change;


    if (item.quantity <= 0) {

        removeFromCart(id);

        return;

    }


    saveCart(cart);


    displayCart();

    displayCheckout();

}


/* =====================================================
   CART SUBTOTAL
===================================================== */

function getCartTotal() {

    const cart =
        getCart();


    return cart.reduce(

        (total, item) =>

            total +
            Number(item.price) *
            Number(item.quantity),

        0

    );

}


/* =====================================================
   DELIVERY FEE
===================================================== */

function getDeliveryFee() {

    const cart =
        getCart();


    if (cart.length === 0) {

        return 0;

    }


    return DELIVERY_FEE;

}


/* =====================================================
   GRAND TOTAL
===================================================== */

function getGrandTotal() {

    const subtotal =
        getCartTotal();


    const deliveryFee =
        getDeliveryFee();


    return subtotal + deliveryFee;

}


/* =====================================================
   CART QUANTITY
===================================================== */

function getCartQuantity() {

    const cart =
        getCart();


    return cart.reduce(

        (total, item) =>

            total +
            Number(item.quantity),

        0

    );

}


/* =====================================================
   UPDATE CART COUNT
===================================================== */

function updateCartCount() {

    const elements =
        document.querySelectorAll(
            "#cartCount"
        );


    const quantity =
        getCartQuantity();


    elements.forEach(
        element => {

            element.textContent =
                quantity;

        }
    );

}


/* =====================================================
   CURRENCY
===================================================== */

function formatMoney(amount) {

    return "₦" +
        Number(amount).toLocaleString(
            "en-NG"
        );

}


/* =====================================================
   PRODUCT IMAGE
===================================================== */

function getProductImage(product) {

    if (product.image) {

        let imagePath =
            product.image;


        /*
         * Images uploaded through PHP are
         * stored only as a filename.
         *
         * Example:
         * product_123.webp
         *
         * We therefore add the correct
         * folder path automatically.
         */

        if (
            !imagePath.startsWith("http://") &&
            !imagePath.startsWith("https://") &&
            !imagePath.startsWith("data:") &&
            !imagePath.startsWith("images/")
        ) {

            imagePath =
                "images/products/" +
                imagePath;

        }


        return `

            <img
                src="${imagePath}"
                alt="${product.name}"
                class="product-real-image"

                onerror="
                    this.style.display='none';
                    this.nextElementSibling.style.display='flex';
                "
            >

            <div
                class="image-placeholder"
                style="display:none;"
            >
                ${product.icon || "🛍️"}
            </div>

        `;

    }


    return `

        <div class="image-placeholder">

            ${product.icon || "🛍️"}

        </div>

    `;

}


/* =====================================================
   PRODUCT CARD
===================================================== */

function createProductCard(product) {

    return `

        <div class="product-card">

            <div class="product-image">

                ${getProductImage(product)}

            </div>


            <div class="product-info">

                <small>
                    ${product.category || "Product"}
                </small>


                <h3>
                    ${product.name}
                </h3>


                <p>
                    ${product.description || ""}
                </p>


                <div class="price">

                    ${formatMoney(product.price)}

                </div>


                <div class="product-buttons">

                    <a
                        href="product.html?id=${product.id}"
                        class="btn"
                    >
                        View
                    </a>


                    <button
                        class="btn"
                        type="button"
                        onclick="addToCart(${product.id})"
                    >
                        Add
                    </button>

                </div>

            </div>

        </div>

    `;

}


/* =====================================================
   HOME PRODUCTS
===================================================== */

function displayFeaturedProducts() {

    const container =
        document.getElementById(
            "featuredProducts"
        );


    if (!container) {

        return;

    }


    const products =
        getProducts().slice(0, 4);


    if (products.length === 0) {

        container.innerHTML = `

            <div class="empty">

                <h2>
                    No products available
                </h2>

                <p>
                    Products will appear here when added.
                </p>

            </div>

        `;

        return;

    }


    container.innerHTML =
        products
            .map(createProductCard)
            .join("");

}


/* =====================================================
   SHOP
===================================================== */

function displayShopProducts() {

    const container =
        document.getElementById(
            "shopProducts"
        );


    if (!container) {

        return;

    }


    let products =
        [...getProducts()];


    const searchInput =
        document.getElementById(
            "searchInput"
        );


    const categoryFilter =
        document.getElementById(
            "categoryFilter"
        );


    const sortProducts =
        document.getElementById(
            "sortProducts"
        );


    const search =
        searchInput
            ? searchInput.value
                .toLowerCase()
                .trim()
            : "";


    const category =
        categoryFilter
            ? categoryFilter.value
            : "all";


    const sort =
        sortProducts
            ? sortProducts.value
            : "";


    /* =========================
       SEARCH
    ========================== */

    if (search) {

        products =
            products.filter(
                product => {

                    const name =
                        String(
                            product.name || ""
                        ).toLowerCase();


                    const description =
                        String(
                            product.description || ""
                        ).toLowerCase();


                    const productCategory =
                        String(
                            product.category || ""
                        ).toLowerCase();


                    return (

                        name.includes(search) ||

                        description.includes(search) ||

                        productCategory.includes(search)

                    );

                }
            );

    }


    /* =========================
       CATEGORY
    ========================== */

    if (
        category &&
        category !== "all"
    ) {

        products =
            products.filter(
                product =>
                    String(
                        product.category
                    ).toLowerCase() ===
                    String(
                        category
                    ).toLowerCase()
            );

    }


    /* =========================
       SORT
    ========================== */

    if (sort === "low") {

        products.sort(
            (a, b) =>
                Number(a.price) -
                Number(b.price)
        );

    }


    if (sort === "high") {

        products.sort(
            (a, b) =>
                Number(b.price) -
                Number(a.price)
        );

    }


    if (sort === "name") {

        products.sort(
            (a, b) =>
                String(a.name)
                    .localeCompare(
                        String(b.name)
                    )
        );

    }


    /* =========================
       NO PRODUCTS
    ========================== */

    if (products.length === 0) {

        container.innerHTML = `

            <div class="empty">

                <h2>
                    No products found
                </h2>

                <p>
                    Try another search or category.
                </p>

            </div>

        `;

        return;

    }


    /* =========================
       DISPLAY PRODUCTS
    ========================== */

    container.innerHTML =
        products
            .map(createProductCard)
            .join("");

}


/* =====================================================
   PRODUCT DETAILS
===================================================== */

function displayProductDetails() {

    const container =
        document.getElementById(
            "productDetails"
        );


    if (!container) {

        return;

    }


    const params =
        new URLSearchParams(
            window.location.search
        );


    const id =
        Number(
            params.get("id")
        );


    const product =
        getProducts().find(
            item =>
                item.id === id
        );


    if (!product) {

        container.innerHTML = `

            <div class="empty">

                <h2>
                    Product Not Found
                </h2>


                <a
                    href="shop.html"
                    class="btn"
                >
                    Return to Shop
                </a>

            </div>

        `;

        return;

    }


    container.innerHTML = `

        <div class="product-detail">


            <div class="detail-image">

                ${getProductImage(product)}

            </div>


            <div class="detail-info">

                <p class="eyebrow">

                    ${product.category || "Product"}

                </p>


                <h1>

                    ${product.name}

                </h1>


                <p>

                    ${product.description || ""}

                </p>


                <div class="price">

                    ${formatMoney(product.price)}

                </div>


                <div class="quantity-box">

                    <button
                        type="button"
                        onclick="changeDetailQuantity(-1)"
                    >
                        −
                    </button>


                    <span id="detailQuantity">

                        1

                    </span>


                    <button
                        type="button"
                        onclick="changeDetailQuantity(1)"
                    >
                        +
                    </button>

                </div>


                <button
                    class="btn"
                    type="button"
                    onclick="addProductFromDetails(${product.id})"
                >
                    Add To Cart
                </button>


                <a
                    href="cart.html"
                    class="btn outline"
                >
                    Go To Cart
                </a>

            </div>

        </div>

    `;

}


/* =====================================================
   PRODUCT DETAIL QUANTITY
===================================================== */

let detailQuantity = 1;


function changeDetailQuantity(change) {

    detailQuantity += change;


    if (detailQuantity < 1) {

        detailQuantity = 1;

    }


    const element =
        document.getElementById(
            "detailQuantity"
        );


    if (element) {

        element.textContent =
            detailQuantity;

    }

}


/* =====================================================
   ADD PRODUCT FROM DETAILS
===================================================== */

function addProductFromDetails(id) {

    const products =
        getProducts();


    const product =
        products.find(
            item =>
                item.id === Number(id)
        );


    if (!product) {

        alert(
            "Product could not be found."
        );

        return;

    }


    const cart =
        getCart();


    const existing =
        cart.find(
            item =>
                item.id === product.id
        );


    if (existing) {

        existing.quantity +=
            detailQuantity;

    } else {

        cart.push({

            ...product,

            quantity:
                detailQuantity

        });

    }


    saveCart(cart);


    detailQuantity = 1;


    alert(
        "Product added to cart."
    );

}


/* =====================================================
   DISPLAY CART
===================================================== */

function displayCart() {

    const container =
        document.getElementById(
            "cartContainer"
        );


    if (!container) {

        return;

    }


    const cart =
        getCart();


    if (cart.length === 0) {

        container.innerHTML = `

            <div class="card empty">

                <h2>
                    Your cart is empty
                </h2>


                <p>
                    You haven't added anything yet.
                </p>


                <br>


                <a
                    href="shop.html"
                    class="btn"
                >
                    Start Shopping
                </a>

            </div>

        `;

        return;

    }


    let html = `

        <div class="card">

    `;


    cart.forEach(item => {

        html += `

            <div class="cart-row">


                <div class="cart-thumb">

                    ${getProductImage(item)}

                </div>


                <div>

                    <h3>
                        ${item.name}
                    </h3>


                    <p>
                        ${formatMoney(item.price)}
                    </p>

                </div>


                <div class="quantity-box">


                    <button
                        type="button"
                        onclick="changeQuantity(${item.id}, -1)"
                    >
                        −
                    </button>


                    <span>
                        ${item.quantity}
                    </span>


                    <button
                        type="button"
                        onclick="changeQuantity(${item.id}, 1)"
                    >
                        +
                    </button>


                </div>


                <strong>

                    ${formatMoney(
                        Number(item.price) *
                        Number(item.quantity)
                    )}

                </strong>


                <button
                    class="remove-btn"
                    type="button"
                    onclick="removeFromCart(${item.id})"
                    title="Remove"
                >
                    🗑️
                </button>


            </div>

        `;

    });


    html += `

        </div>


        <div class="cart-total">


            <p>

                Total Items:

                <strong>
                    ${getCartQuantity()}
                </strong>

            </p>


            <h2>

                Subtotal:

                ${formatMoney(
                    getCartTotal()
                )}

            </h2>


            <a
                href="checkout.html"
                class="btn"
            >
                Proceed To Checkout
            </a>


        </div>

    `;


    container.innerHTML =
        html;

}


/* =====================================================
   CHECKOUT
===================================================== */

function displayCheckout() {

    const container =
        document.getElementById(
            "checkoutSummary"
        );


    if (!container) {

        return;

    }


    const cart =
        getCart();


    if (cart.length === 0) {

        container.innerHTML = `

            <div class="empty">

                <h3>
                    Your cart is empty.
                </h3>


                <a
                    href="shop.html"
                    class="btn"
                >
                    Go Shopping
                </a>

            </div>

        `;

        return;

    }


    let html = "";


    /* =========================
       PRODUCTS
    ========================== */

    cart.forEach(item => {

        html += `

            <div class="summary-row">

                <span>

                    ${item.name}
                    × ${item.quantity}

                </span>


                <strong>

                    ${formatMoney(
                        Number(item.price) *
                        Number(item.quantity)
                    )}

                </strong>

            </div>

        `;

    });


    /* =========================
       SUBTOTAL
    ========================== */

    html += `

        <div class="summary-row">

            <span>
                Subtotal
            </span>


            <strong>

                ${formatMoney(
                    getCartTotal()
                )}

            </strong>

        </div>

    `;


    /* =========================
       DELIVERY FEE
    ========================== */

    html += `

        <div class="summary-row">

            <span>
                Delivery Fee
            </span>


            <strong>

                ${formatMoney(
                    getDeliveryFee()
                )}

            </strong>

        </div>

    `;


    /* =========================
       GRAND TOTAL
    ========================== */

    html += `

        <div
            class="summary-total"
            style="
                margin-top: 15px;
                padding-top: 15px;
                border-top: 2px solid #ddd;
            "
        >

            <strong>
                Total
            </strong>


            <strong>

                ${formatMoney(
                    getGrandTotal()
                )}

            </strong>

        </div>

    `;


    container.innerHTML =
        html;

}


/* =====================================================
   HANDLE CHECKOUT - FLUTTERWAVE
===================================================== */

function handleCheckout(event) {

    event.preventDefault();


    const cart =
        getCart();


    if (cart.length === 0) {

        alert(
            "Your cart is empty."
        );

        return;

    }


    /* =========================
       CUSTOMER INFORMATION
    ========================== */

    const name =
        document.getElementById(
            "checkoutName"
        ).value.trim();


    const email =
        document.getElementById(
            "checkoutEmail"
        ).value.trim();


    const phone =
        document.getElementById(
            "checkoutPhone"
        ).value.trim();


    const address =
        document.getElementById(
            "checkoutAddress"
        ).value.trim();


    const city =
        document.getElementById(
            "checkoutCity"
        ).value.trim();


    const paymentMethod =
        document.getElementById(
            "paymentMethod"
        ).value;


    /* =========================
       VALIDATE INFORMATION
    ========================== */

    if (
        !name ||
        !email ||
        !phone ||
        !address ||
        !city
    ) {

        alert(
            "Please fill in all delivery information."
        );

        return;

    }


    /* =========================
       PAYMENT METHOD
    ========================== */

    if (
        paymentMethod !==
        "Flutterwave"
    ) {

        alert(
            "Please select Pay with Flutterwave."
        );

        return;

    }


    /* =========================
       CALCULATE TOTAL
    ========================== */

    const subtotal =
        getCartTotal();


    const deliveryFee =
        getDeliveryFee();


    const total =
        subtotal +
        deliveryFee;


    /* =========================
       PAYMENT AMOUNT
    ========================== */

    const amountInput =
        document.getElementById(
            "paymentAmount"
        );


    if (!amountInput) {

        alert(
            "Payment amount field was not found."
        );

        return;

    }


    amountInput.value =
        total;


    /* =========================
       TEMPORARY PENDING ORDER
    ========================== */

    const pendingOrder = {

        id: Date.now(),

        customer: name,

        email: email,

        phone: phone,

        address: address,

        city: city,

        subtotal: subtotal,

        deliveryFee: deliveryFee,

        total: total,

        paymentMethod:
            "Flutterwave",

        items: cart,

        date:
            new Date().toISOString()

    };


    localStorage.setItem(

        "northVaultPendingOrder",

        JSON.stringify(
            pendingOrder
        )

    );


    console.log(

        "North Vault payment amount:",

        formatMoney(total)

    );


    /* =========================
       SEND FORM TO PHP
    ========================== */

    event.target.submit();

}


/* =====================================================
   REGISTER
===================================================== */

function handleRegister(event) {

    event.preventDefault();


    const name =
        document.getElementById(
            "registerName"
        ).value;


    const email =
        document.getElementById(
            "registerEmail"
        ).value;


    const password =
        document.getElementById(
            "registerPassword"
        ).value;


    const confirm =
        document.getElementById(
            "registerConfirm"
        ).value;


    if (password !== confirm) {

        alert(
            "Passwords do not match."
        );

        return;

    }


    const users =
        JSON.parse(
            localStorage.getItem(
                "northVaultUsers"
            )
        ) || [];


    const existing =
        users.find(
            user =>
                user.email === email
        );


    if (existing) {

        alert(
            "An account with this email already exists."
        );

        return;

    }


    users.push({

        id: Date.now(),

        name: name,

        email: email,

        password: password

    });


    localStorage.setItem(

        "northVaultUsers",

        JSON.stringify(users)

    );


    localStorage.setItem(

        "northVaultCurrentUser",

        JSON.stringify({

            name: name,

            email: email

        })

    );


    alert(
        "Account created successfully."
    );


    window.location.href =
        "profile.html";

}


/* =====================================================
   LOGIN
===================================================== */

function handleLogin(event) {

    event.preventDefault();


    const email =
        document.getElementById(
            "loginEmail"
        ).value;


    const password =
        document.getElementById(
            "loginPassword"
        ).value;


    const users =
        JSON.parse(
            localStorage.getItem(
                "northVaultUsers"
            )
        ) || [];


    const user =
        users.find(

            item =>

                item.email === email &&

                item.password === password

        );


    if (!user) {

        alert(
            "Incorrect email or password."
        );

        return;

    }


    localStorage.setItem(

        "northVaultCurrentUser",

        JSON.stringify({

            name: user.name,

            email: user.email

        })

    );


    alert(
        "Login successful."
    );


    window.location.href =
        "profile.html";

}


/* =====================================================
   PROFILE
===================================================== */

function displayProfile() {

    const container =
        document.getElementById(
            "profileContent"
        );


    if (!container) {

        return;

    }


    const user =
        JSON.parse(
            localStorage.getItem(
                "northVaultCurrentUser"
            )
        );


    if (!user) {

        container.innerHTML = `

            <div class="card empty">

                <div class="avatar">
                    👤
                </div>


                <h2>
                    You are not logged in.
                </h2>


                <p>
                    Login or create an account to continue.
                </p>


                <br>


                <a
                    href="login.php"
                    class="btn"
                >
                    Login
                </a>


                <a
                    href="register.php"
                    class="btn outline"
                >
                    Register
                </a>

            </div>

        `;

        return;

    }


    container.innerHTML = `

        <div class="profile-grid">


            <div class="card profile-card">

                <div class="avatar">
                    👤
                </div>


                <h2>
                    ${user.name}
                </h2>


                <p>
                    ${user.email}
                </p>


                <button
                    class="btn"
                    type="button"
                    onclick="logout()"
                >
                    Logout
                </button>

            </div>


            <div class="card">

                <h2>
                    Account
                </h2>


                <p>

                    <strong>
                        Name:
                    </strong>

                    ${user.name}

                </p>


                <p>

                    <strong>
                        Email:
                    </strong>

                    ${user.email}

                </p>


                <hr>


                <br>


                <a
                    href="shop.html"
                    class="menu-item"
                >
                    🛍️ Continue Shopping
                </a>


                <a
                    href="cart.html"
                    class="menu-item"
                >
                    🛒 View Cart
                </a>

            </div>

        </div>

    `;

}


/* =====================================================
   LOGOUT
===================================================== */

function logout() {

    localStorage.removeItem(
        "northVaultCurrentUser"
    );


    alert(
        "You have been logged out."
    );


    window.location.href =
        "index.html";

}


/* =====================================================
   CONTACT FORM
===================================================== */

function handleContact(event) {

    event.preventDefault();


    const name =
        document.getElementById(
            "contactName"
        ).value;


    alert(

        "Thank you " +
        name +
        ". Your message has been received."

    );


    event.target.reset();

}


/* =====================================================
   ADMIN DASHBOARD
===================================================== */

function displayAdmin() {

    const products =
        getProducts();


    const cart =
        getCart();


    const users =
        JSON.parse(
            localStorage.getItem(
                "northVaultUsers"
            )
        ) || [];


    const orders =
        JSON.parse(
            localStorage.getItem(
                "northVaultOrders"
            )
        ) || [];


    const productCount =
        document.getElementById(
            "adminProductCount"
        );


    const cartCount =
        document.getElementById(
            "adminCartCount"
        );


    const customerCount =
        document.getElementById(
            "adminCustomerCount"
        );


    const revenue =
        document.getElementById(
            "adminRevenue"
        );


    if (productCount) {

        productCount.textContent =
            products.length;

    }


    if (cartCount) {

        cartCount.textContent =
            getCartQuantity();

    }


    if (customerCount) {

        customerCount.textContent =
            users.length;

    }


    const totalRevenue =
        orders.reduce(

            (total, order) =>

                total +
                Number(
                    order.total || 0
                ),

            0

        );


    if (revenue) {

        revenue.textContent =
            formatMoney(
                totalRevenue
            );

    }


    displayAdminProducts();

}


/* =====================================================
   ADMIN PRODUCT LIST
===================================================== */

function displayAdminProducts() {

    const container =
        document.getElementById(
            "adminProducts"
        );


    if (!container) {

        return;

    }


    const products =
        getProducts();


    if (products.length === 0) {

        container.innerHTML =
            "<p>No products available.</p>";

        return;

    }


    container.innerHTML =

        products
            .map(
                product => `

                <div class="admin-item">


                    <div class="admin-product-left">


                        <div class="admin-product-image">

                            ${getProductImage(product)}

                        </div>


                        <div>

                            <strong>
                                ${product.name}
                            </strong>


                            <br>


                            <small>

                                ${product.category}

                                —

                                ${formatMoney(
                                    product.price
                                )}

                            </small>

                        </div>


                    </div>


                    <button
                        class="delete-btn"
                        type="button"
                        onclick="deleteProduct(${product.id})"
                    >
                        Delete
                    </button>


                </div>

            `
            )
            .join("");

}


/* =====================================================
   IMAGE FILE TO BASE64
   LEGACY FRONTEND FUNCTION
===================================================== */

function readImageFile(file) {

    return new Promise(
        (resolve, reject) => {

            if (!file) {

                reject(
                    new Error(
                        "No image selected."
                    )
                );

                return;

            }


            if (
                !file.type.startsWith(
                    "image/"
                )
            ) {

                reject(
                    new Error(
                        "Please select a valid image file."
                    )
                );

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function () {

                    resolve(
                        reader.result
                    );

                };


            reader.onerror =
                function () {

                    reject(
                        new Error(
                            "Could not read the image."
                        )
                    );

                };


            reader.readAsDataURL(file);

        }
    );

}


/* =====================================================
   IMAGE PREVIEW
===================================================== */

function setupImagePreview() {

    const imageInput =
        document.getElementById(
            "adminProductImage"
        );


    const preview =
        document.getElementById(
            "adminImagePreview"
        );


    if (
        !imageInput ||
        !preview
    ) {

        return;

    }


    imageInput.addEventListener(

        "change",

        function () {

            const file =
                this.files[0];


            if (!file) {

                preview.src = "";

                preview.style.display =
                    "none";

                return;

            }


            if (
                !file.type.startsWith(
                    "image/"
                )
            ) {

                alert(
                    "Please select an image file."
                );


                this.value = "";


                preview.src = "";


                preview.style.display =
                    "none";


                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function (event) {

                    preview.src =
                        event.target.result;


                    preview.style.display =
                        "block";

                };


            reader.readAsDataURL(file);

        }

    );

}


/* =====================================================
   LEGACY ADMIN ADD PRODUCT
===================================================== */

async function addAdminProduct(event) {

    /*
     * Your real Admin Product page is now:
     *
     * admin/add-product.php
     *
     * Therefore this function is kept only
     * for compatibility with any old form.
     */

    event.preventDefault();


    alert(
        "Please use the North Vault Admin Add Product page to add products."
    );

}


/* =====================================================
   LEGACY DELETE PRODUCT
===================================================== */

function deleteProduct(id) {

    /*
     * Product deletion is now handled by:
     *
     * admin/delete-product.php
     *
     * This function is kept so old frontend
     * buttons do not break.
     */

    const answer =
        confirm(
            "Product management is now handled from the Admin Dashboard. Open the Products page?"
        );


    if (!answer) {

        return;

    }


    window.location.href =
        "products.php";

}


/* =====================================================
   DARK MODE
===================================================== */

function toggleDarkMode() {

    document.body.classList.toggle(
        "dark"
    );


    if (
        document.body.classList.contains(
            "dark"
        )
    ) {

        localStorage.setItem(
            "northVaultDark",
            "true"
        );

    } else {

        localStorage.setItem(
            "northVaultDark",
            "false"
        );

    }

}


function loadDarkMode() {

    const dark =
        localStorage.getItem(
            "northVaultDark"
        );


    if (dark === "true") {

        document.body.classList.add(
            "dark"
        );

    }

}


/* =====================================================
   INITIALIZE WEBSITE
===================================================== */

document.addEventListener(

    "DOMContentLoaded",

    function () {


        /* =============================================
           LOAD PRODUCTS FROM MYSQL
        ============================================= */

        loadProductsFromDatabase();


        /* =============================================
           CART
        ============================================= */

        updateCartCount();


        displayCart();


        displayCheckout();


        /* =============================================
           PROFILE
        ============================================= */

        displayProfile();


        /* =============================================
           DARK MODE
        ============================================= */

        loadDarkMode();


        /* =============================================
           IMAGE PREVIEW
        ============================================= */

        setupImagePreview();


        /* =============================================
           CHECKOUT
        ============================================= */

        const checkoutForm =
            document.getElementById(
                "checkoutForm"
            );


        if (checkoutForm) {

            checkoutForm.addEventListener(
                "submit",
                handleCheckout
            );

        }


        /* =============================================
           REGISTER
        ============================================= */

        const registerForm =
            document.getElementById(
                "registerForm"
            );


        if (registerForm) {

            registerForm.addEventListener(
                "submit",
                handleRegister
            );

        }


        /* =============================================
           LOGIN
        ============================================= */

        const loginForm =
            document.getElementById(
                "loginForm"
            );


        if (loginForm) {

            loginForm.addEventListener(
                "submit",
                handleLogin
            );

        }


        /* =============================================
           CONTACT
        ============================================= */

        const contactForm =
            document.getElementById(
                "contactForm"
            );


        if (contactForm) {

            contactForm.addEventListener(
                "submit",
                handleContact
            );

        }


        /* =============================================
           SHOP SEARCH
        ============================================= */

        const searchInput =
            document.getElementById(
                "searchInput"
            );


        const categoryFilter =
            document.getElementById(
                "categoryFilter"
            );


        const sortProducts =
            document.getElementById(
                "sortProducts"
            );


        if (searchInput) {

            searchInput.addEventListener(
                "input",
                displayShopProducts
            );

        }


        if (categoryFilter) {

            categoryFilter.addEventListener(
                "change",
                displayShopProducts
            );

        }


        if (sortProducts) {

            sortProducts.addEventListener(
                "change",
                displayShopProducts
            );

        }

    }

);