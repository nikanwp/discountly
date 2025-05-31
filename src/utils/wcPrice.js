const wcPriceFormatter = (price) => {
    if (price === undefined || price === null || isNaN(price)) return '0';

    const decimalSeparator = nwpdiscountly.wc_decimal_separator || '.';
    const decimals = Number.isNaN(parseInt(nwpdiscountly.wc_number_of_decimals, 10)) ? 2 : parseInt(nwpdiscountly.wc_number_of_decimals, 10);

    let formattedPrice = Number(price).toFixed(decimals);

    if (decimals > 0 && decimalSeparator !== '.') {
        formattedPrice = formattedPrice.replace('.', decimalSeparator);
    }

    return formattedPrice;
};

export default wcPriceFormatter;
