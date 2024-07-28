const { registerPaymentMethod } = wc.wcBlocksRegistry;
const { getSetting } = wc.wcSettings;

const boletoSettings = getSetting('endopay_boleto_data', {});
const creditCardSettings = getSetting('endopay_credit_card_data', {});

const boletoLabel = boletoSettings.title;
const creditCardLabel = creditCardSettings.title;

const Content = ({ description }) => {
    return description || '';
};

const Label = ({ label }) => {
    const { PaymentMethodLabel } = wc.components;
    return <PaymentMethodLabel text={label} />;
};

const CreditCardFields = () => {
    return (
        <div>
            <div className="form-row form-row-wide">
                <label htmlFor="endopay-cc-name">{ 'Cardholder Name' } <span className="required">*</span></label>
                <input id="endopay-cc-name" name="endopay_cc_name" type="text" autoComplete="cc-name" />
            </div>
            <div className="form-row form-row-wide">
                <label htmlFor="endopay-cc-number">{ 'Card Number' } <span className="required">*</span></label>
                <input id="endopay-cc-number" name="endopay_cc_number" type="text" autoComplete="cc-number" />
            </div>
            <div className="form-row form-row-first">
                <label htmlFor="endopay-cc-expiry">{ 'Expiry Date' } <span className="required">*</span></label>
                <input id="endopay-cc-expiry" name="endopay_cc_expiry" type="text" autoComplete="cc-exp" placeholder="MM / YY" />
            </div>
            <div className="form-row form-row-last">
                <label htmlFor="endopay-cc-cvc">{ 'Card Code (CVC)' } <span className="required">*</span></label>
                <input id="endopay-cc-cvc" name="endopay_cc_cvc" type="text" autoComplete="cc-csc" />
            </div>
            <div className="clear"></div>
        </div>
    );
};

registerPaymentMethod({
    name: "endopay_boleto",
    label: <Label label={boletoLabel} />,
    content: <Content description={boletoSettings.description} />,
    edit: <Content description={boletoSettings.description} />,
    canMakePayment: () => true,
    ariaLabel: boletoLabel,
    supports: {
        features: boletoSettings.supports,
    }
});

registerPaymentMethod({
    name: "endopay_credit_card",
    label: <Label label={creditCardLabel} />,
    content: <CreditCardFields />,
    edit: <CreditCardFields />,
    canMakePayment: () => true,
    ariaLabel: creditCardLabel,
    supports: {
        features: creditCardSettings.supports,
    }
});
