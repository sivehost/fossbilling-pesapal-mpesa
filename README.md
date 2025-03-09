### **📌 FOSSBilling Mpesa Payment Module for Pesapal**  
**Seamless payment processing for businesses in Kenya, Uganda, Tanzania, Malawi, Rwanda, Zambia, and Zimbabwe.**  

This module integrates **Pesapal** with **FOSSBilling**, enabling you to accept payments via **credit cards, debit cards, and mobile money**. Payments are settled directly into your **local bank account**.

![Claim Status Graph](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/pAccount.png?raw=true)

![Invoice PDF Export](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/pcard.png?raw=true)

![Invoice Status Graph](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/pCart.png?raw=true)

![Claims page](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/PChoose.png?raw=true)

![Invoices page](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/pConfig.png?raw=true)

![Config page](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/pPayNow.png?raw=true)

![Config page](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/pPayWithMpesa.png?raw=true)

---

## **🌍 Supported Payment Methods**
### **📲 Mobile Money Payments**
✅ **MPESA (Kenya, Tanzania)**  
✅ **Airtel Money (Multiple countries)**  
✅ **MTN Money**  
✅ **Vodacom MPESA**  
✅ **Tigo Pesa**  

### **💳 Card Payments**
✅ **Visa**  
✅ **MasterCard**  
✅ **American Express**  
✅ **Diners Club**  
✅ **JCB Cards**  

---

## **📥 Installation**
### **1️⃣ Install via FOSSBilling Extension Directory**
The easiest way to install this module is using the **FOSSBilling extension directory**.  
1. Upload the Mpesa.php file into /library/Payment/Adapter/ and then Go to **FOSSBilling Admin Panel**.  
2. Navigate to **System → Payment Gateways**.  
3. Search for **Pesapal** and click **Install**.  
4. Configure your API credentials and preferences.  

---

### **2️⃣ Manual Installation**
1. **Download the latest release** from [GitHub Releases](https://github.com/sivehost/fossbilling-pesapal-mpesa/releases).  
2. **Create a new folder** named **Pesapal** inside your **FOSSBilling installation**:  
   ```
   /library/Payment/Adapter/Pesapal/
   ```
3. **Extract the downloaded files** into the new **Pesapal** directory.  
4. Go to **Admin Panel → System → Payment Gateways**.  
5. Find **Pesapal** under the **"New Payment Gateway"** tab.  
6. Click the **cog icon** to **install and configure Pesapal**.  

---

## **⚙️ Configuration**
Once installed, configure Pesapal in **FOSSBilling**:  

### **🔑 Enter API Credentials**
1. Navigate to **System → Payment Gateways → Pesapal**.  
2. Enter your **Pesapal API Keys** .  
   - **Find these in your Pesapal Dashboard**.  

### **🔧 Customize Preferences**
- Enable **sandbox mode** for testing transactions before going live.  
- Enable **transaction logging** for debugging.  

### **✅ Save & Test**
1. Click **Save Changes**.  
2. Run a **test transaction** in sandbox mode.  
3. Once successful, switch to **live mode** to start accepting real payments.  

---

## **🚀 Why Use This Module?**
✔ **Fast & Secure**: Accept payments in multiple currencies and payment methods.  
✔ **Easy Setup**: Install in just a few clicks.  
✔ **Seamless Integration**: Works directly with **FOSSBilling’s invoicing system**.  
✔ **Automatic Settlements**: Funds are settled into your **local bank account**.  

---

## **🤝 Contributing**
We welcome contributions! 🚀  
- **Found a bug?** Open an issue in GitHub.  
- **Want to improve the module?** Submit a pull request.  

To contribute:  
```bash
git clone https://github.com/sivehost/fossbilling-pesapal-mpesa.git
cd fossbilling-pesapal-mpesa
git checkout -b feature-branch
```
Make your changes, commit, and submit a pull request.

---

## **📜 License**
This module is licensed under the **GNU General Public License v3.0**.  
See [LICENSE](https://github.com/sivehost/fossbilling-pesapal-mpesa/blob/main/LICENSE) for details.

---

## **📞 Support**
For help, visit:  
- **Pesapal Developer Docs**: [https://developer.pesapal.com/](https://developer.pesapal.com/)  
- **FOSSBilling Community**: [https://fossbilling.org/](https://fossbilling.org/)  
- **Sive.Host Support**: [https://sive.host/](https://Sive.Host/)  

---

### **📢 Start Accepting Payments Today!**
➡ **[Download the latest version](https://github.com/sivehost/fossbilling-pesapal-mpesa/releases)** and get started! 🚀  

---
