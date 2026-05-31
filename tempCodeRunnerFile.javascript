<script>
    let prices = [];
    let names = [];

    for (let i = 0; i < 3; i++) {
        let name = prompt("Enter item " + (i+1) + " name:");
        let price = parseFloat(prompt("Enter price of " + name + ":"));

        if (isNaN(price)) {
            throw new Error("InvalidPriceException");
        }

        names.push(name);
        prices.push(price);
    }

    let total = prices.reduce((a, b) => a + b, 0);
    let max = Math.max(...prices);
    let avg = total / 3;

    document.write("Total: " + total + "<br>");
    document.write("Maximum: " + max + "<br>");
    document.write("Average: " + avg.toFixed(2));
</script>