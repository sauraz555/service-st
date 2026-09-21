(function () {
    "use strict";

    const $ = (id) => document.getElementById(id);

    const editableFields = [
        "branch",
        "clientName",
        "visaSubclass",
        "clientAddress",
        "statementDate",
        "completionDate",
        "price",
        "paymentReceived",
        "departmentFees",
        "authorityPayments"
    ];

    const numericFields = [
        "price",
        "paymentReceived",
        "departmentFees",
        "authorityPayments"
    ];

    const apiFieldMap = {
        branch: "branch",
        clientName: "clientName",
        visaSubclass: "visaSubclass",
        clientAddress: "clientAddress",
        statementDate: "statementDate",
        completionDate: "completionDate",
        fixedServiceFee: "price",
        paymentReceived: "paymentReceived",
        operatingTransferred: "operatingTransferred",
        departmentFees: "departmentFees",
        authorityPayments: "authorityPayments"
    };

    function numberValue(id) {
        const element = $(id);
        if (!element) return 0;

        const n = parseFloat(element.value);
        return Number.isFinite(n) ? n : 0;
    }

    function roundMoney(n) {
        return Math.round((n + Number.EPSILON) * 100) / 100;
    }

    function money(n) {
        const sign = n < 0 ? "-" : "";
        return sign + "$" + Math.abs(n).toLocaleString("en-AU", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDate(dateValue) {
        if (!dateValue) return "";

        const parts = dateValue.split("-");
        if (parts.length !== 3) return dateValue;

        const date = new Date(
            Number(parts[0]),
            Number(parts[1]) - 1,
            Number(parts[2])
        );

        return date.toLocaleDateString("en-AU", {
            day: "2-digit",
            month: "long",
            year: "numeric"
        });
    }

    function calculate() {
        // The entered fixed service fee is GST-inclusive.
        const professionalFee = numberValue("price");
        const gst = roundMoney(professionalFee / 11);
        const price = roundMoney(professionalFee - gst);

        const paymentReceived = numberValue("paymentReceived");
        const operatingTransferred = professionalFee;
        const departmentFees = numberValue("departmentFees");
        const authorityPayments = numberValue("authorityPayments");

        // Actual balance still physically held in the client account.
        const clientAccountBalance = roundMoney(
            paymentReceived
            - operatingTransferred
            - departmentFees
            - authorityPayments
        );

        // Overall matter position using the GST-inclusive fixed fee.
        // Negative = client still owes money.
        // Positive = client funds remain after all listed charges.
        const netPosition = roundMoney(
            paymentReceived
            - departmentFees
            - authorityPayments
            - professionalFee
        );

        return {
            price,
            gst,
            professionalFee,
            paymentReceived,
            operatingTransferred,
            departmentFees,
            authorityPayments,
            clientAccountBalance,
            netPosition
        };
    }

    function getSelectedBranch() {
        const branch = $("branch");

        if (!branch || branch.selectedIndex < 0) {
            return { name: "", addressLine1: "", addressLine2: "" };
        }

        const option = branch.options[branch.selectedIndex];

        return {
            name: branch.value || "",
            addressLine1: option ? (option.dataset.address1 || "") : "",
            addressLine2: option ? (option.dataset.address2 || "") : ""
        };
    }

    function syncPrint() {
        const c = calculate();
        const clientName = $("clientName").value.trim();
        const selectedBranch = getSelectedBranch();

        $("pBranchAddressLine1").textContent = selectedBranch.addressLine1;
        $("pBranchAddressLine2").textContent = selectedBranch.addressLine2;

        // Automatically show the total GST-inclusive professional fee to be transferred.
        $("operatingTransferred").value = c.professionalFee ? c.professionalFee.toFixed(2) : "";

        $("pClientName").textContent = clientName;
        $("pVisaSubclass").textContent = $("visaSubclass").value.trim();
        $("pAddress").textContent = $("clientAddress").value.trim();
        $("pStatementDate").textContent = formatDate($("statementDate").value);
        $("pCompletionDate").textContent = formatDate($("completionDate").value);

        $("pPrice").textContent = money(c.price);
        $("pGst").textContent = money(c.gst);
        $("pServiceTotal").textContent = money(c.professionalFee);

        $("pTotalValue").textContent = money(c.professionalFee);
        $("pPaymentReceived").textContent = money(c.paymentReceived);
        $("pOperatingTransferred").textContent = money(c.operatingTransferred);
        $("pDepartmentFees").textContent = money(c.departmentFees);
        $("pAuthorityPayments").textContent = money(c.authorityPayments);
        $("pClientAccountBalance").textContent = money(c.clientAccountBalance);
        $("pProfessionalFee").textContent = money(c.professionalFee);

        const card = $("positionCard");
        card.classList.remove("due", "clear");

        if (c.netPosition < 0) {
            card.classList.add("due");
            $("positionLabel").textContent = "Amount Due";
            $("positionAmount").textContent = money(Math.abs(c.netPosition));
            $("positionNote").textContent =
                "This amount remains payable after allowing for the fixed professional fee, Departmental fees and payments made to authorities shown in this statement. Once received, the professional fee shown above will be eligible for transfer to our Operating Account in accordance with the Service Agreement and applicable requirements.";
        } else if (c.netPosition > 0) {
            $("positionLabel").textContent = "Client Funds Remaining";
            $("positionAmount").textContent = money(c.netPosition);
            $("positionNote").textContent =
                "Any client money remaining after payment of authorised professional fees, Departmental fees and other authorised disbursements will, where refundable, be returned to the client in accordance with the Service Agreement and applicable requirements.";
        } else {
            card.classList.add("clear");
            $("positionLabel").textContent = "Account Settled";
            $("positionAmount").textContent = "$0.00";
            $("positionNote").textContent =
                "No further amount is due and no unused client funds remain after the professional fee, Departmental fees and payments made to authorities shown in this statement.";
        }
    }

    function setToday() {
        const now = new Date();
        const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
            .toISOString()
            .slice(0, 10);

        $("statementDate").value = localDate;
    }

    function printStatement() {
        const form = $("statementForm");

        if (!form.reportValidity()) return false;

        try {
            syncPrint();
            setTimeout(function () {
                window.print();
            }, 50);
            return true;
        } catch (error) {
            console.error("Print preparation error:", error);
            alert("The statement could not be prepared for printing. Please refresh the page and try again.");
            return false;
        }
    }

    function resetStatement() {
        $("statementForm").reset();

        numericFields.forEach((id) => {
            $(id).value = "";
        });

        setToday();
        syncPrint();
    }

    function setData(data) {
        if (!data || typeof data !== "object") return;

        Object.entries(apiFieldMap).forEach(([key, id]) => {
            if (!Object.prototype.hasOwnProperty.call(data, key)) return;
            const element = $(id);
            if (!element) return;
            if (id === "operatingTransferred") return;

            const supplied = data[key];
            element.value = supplied === null || supplied === undefined
                ? ""
                : String(supplied);
        });

        syncPrint();
    }

    function getData() {
        const result = {};

        Object.entries(apiFieldMap).forEach(([key, id]) => {
            result[key] = $(id) ? $(id).value : "";
        });

        result.calculated = calculate();
        return result;
    }

    function initialise() {
        editableFields.forEach((id) => {
            const element = $(id);
            if (!element) return;

            element.addEventListener("input", syncPrint);
            element.addEventListener("change", syncPrint);
        });

        numericFields.forEach((id) => {
            const element = $(id);
            if (!element) return;

            element.addEventListener("focus", function () {
                this.select();
            });
        });

        $("printBtn").addEventListener("click", printStatement);
        $("resetBtn").addEventListener("click", resetStatement);
        window.addEventListener("beforeprint", syncPrint);

        setToday();
        syncPrint();

        // Public API for integrating this module into an existing CRM/system.
        window.InterlaceStatement = {
            setData,
            getData,
            calculate,
            sync: syncPrint,
            print: printStatement,
            reset: resetStatement
        };
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initialise);
    } else {
        initialise();
    }
})();
