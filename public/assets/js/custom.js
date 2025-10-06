function numberFormat(number, decimals, decPoint = ",", thousandsSep = ".") {
    if (!isFinite(number)) return "0";

    const sign = number < 0 ? "-" : "";
    number = Math.abs(+number || 0);

    // Use up to 6 decimals, but trim trailing zeros
    let str = number.toFixed(decimals);
    str = str.replace(/\.?0+$/, ""); // remove trailing zeros and unnecessary decimal point

    // Split integer and decimal parts
    let [intPart, decPart] = str.split(".");

    // Format thousands
    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

    // Combine with correct decimal separator
    return sign + intPart + (decPart ? decPoint + decPart : "");
}
