<?php
declare(strict_types=1);

$company = require __DIR__ . '/config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interlace Studies | Statement of Services</title>
    <link rel="stylesheet" href="assets/css/statement.css">
    <script src="assets/js/statement.js" defer></script>
</head>
<body>
<!-- ======================= STAFF FORM ======================= -->
<div class="app">
    <div class="card">
        <div class="screen-top"></div>

        <div class="screen-header">
            <img src="assets/img/interlace-logo.png" alt="Interlace Studies">
            <div class="screen-title">
                <h1>Statement of Services</h1>
                <p>Complete the details below and generate the final statement.</p>
            </div>
        </div>

        <div class="form-body">
            <form id="statementForm" onsubmit="return false;">
                <div class="section-heading">Client & Matter Details</div>

                <div class="form-grid">
                    <div class="full">
                        <label for="branch">Branch *</label>
                        <select id="branch" name="branch" required>
                            <?php foreach ($company['branches'] as $branchName => $branchData): ?>
                                <option
                                    value="<?= h($branchName) ?>"
                                    data-address1="<?= h($branchData['address_line_1']) ?>"
                                    data-address2="<?= h($branchData['address_line_2']) ?>"
                                    <?= $branchName === $company['default_branch'] ? 'selected' : '' ?>
                                ><?= h($branchName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="clientName">Client Full Name *</label>
                        <input id="clientName" name="client_name" type="text" required placeholder="Enter client full name">
                    </div>

                    <div>
                        <label for="visaSubclass">Matter *</label>
                        <input id="visaSubclass" name="visa_subclass" type="text" required placeholder="e.g. Student Visa Application">
                    </div>

                    <div class="full">
                        <label for="clientAddress">Client Current Address *</label>
                        <textarea id="clientAddress" name="client_address" required placeholder="Enter client's current address"></textarea>
                    </div>

                    <div>
                        <label for="statementDate">Statement Date *</label>
                        <input id="statementDate" name="statement_date" type="date" required>
                    </div>

                    <div>
                        <label for="completionDate">Completion Date *</label>
                        <input id="completionDate" name="completion_date" type="date" required>
                    </div>
                </div>

                <hr class="divider">

                <div class="section-heading">Financial Details</div>
                <div class="helper">The service is charged as one fixed-fee full-service package. The fixed fee entered below is inclusive of GST, and balances are calculated automatically.</div>

                <div class="form-grid">
                    <div>
                        <label for="price">Fixed Service Fee (including GST) *</label>
                        <div class="money">
                            <input id="price" name="fixed_service_fee" type="number" min="0" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    <div>
                        <label for="paymentReceived">Total Payment Received in Client Account</label>
                        <div class="money">
                            <input id="paymentReceived" name="payment_received" type="number" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label for="operatingTransferred">Total Professional Fee Eligible for Transfer to Operating Account</label>
                        <div class="money">
                            <input id="operatingTransferred" name="operating_transferred" type="number" min="0" step="0.01" placeholder="0.00" readonly>
                        </div>
                    </div>

                    <div>
                        <label for="departmentFees">Departmental Fees Used for Application</label>
                        <div class="money">
                            <input id="departmentFees" name="department_fees" type="number" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label for="authorityPayments">Other Payments made to Authorities / Third Parties on behalf of Client*</label>
                        <div class="money">
                            <input id="authorityPayments" name="authority_payments" type="number" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="reset-btn" id="resetBtn">Reset</button>
                    <button type="button" class="print-btn" id="printBtn">Print / Save as PDF</button>
                </div>

                <div class="small-note">Only the final statement appears in the printed/PDF version.</div>
            </form>
        </div>
    </div>
</div>

<!-- ======================= FINAL PRINT ======================= -->
<section id="printPage">
    <div class="top-brand-line"></div>

    <div class="page-content">
        <header class="letterhead">
            <div class="company-column">
                <div>
                    <div class="company-name"><?= h($company['name']) ?></div>
                    <div class="branch-address">
                        <div id="pBranchAddressLine1"></div>
                        <div id="pBranchAddressLine2"></div>
                    </div>
                    <div class="company-abn"><strong>ABN:</strong> <?= h($company['abn']) ?></div>
                </div>

                <div class="email-block">
                    <span class="email-label">Email:</span>
                    <span class="mail"><?= h($company['emails'][0]) ?></span>,
                    <span class="mail"><?= h($company['emails'][1]) ?></span>
                </div>
            </div>

            <div class="contact-column">
                <img class="print-logo" src="assets/img/interlace-logo.png" alt="Interlace Studies">

                <div class="phone-block">
                    <strong>Business number:</strong> <?= h($company['business_number']) ?><br>
                    <strong>Phone number:</strong> <?= h($company['phone']) ?>
                </div>
            </div>
        </header>

        <section class="document-title">
            <h1>Statement of Services</h1>
            <div class="title-rule"></div>

            <div class="rma-grid">
                <?php foreach ($company['agents'] as $agent): ?>
                    <div><?= h($agent['name']) ?> MARN: <?= h($agent['marn']) ?></div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="matter-card">
            <div class="matter-grid">
                <div class="matter-cell">
                    <div class="matter-section-heading">Client</div>
                    <div class="matter-value matter-value-primary" id="pClientName"></div>
                </div>

                <div class="matter-cell">
                    <div class="matter-section-heading">Matter</div>
                    <div class="matter-value matter-value-primary" id="pVisaSubclass"></div>
                </div>

                <div class="matter-cell">
                    <div class="matter-section-heading">Address</div>
                    <div class="matter-value matter-value-secondary" id="pAddress"></div>
                </div>

                <div class="matter-cell">
                    <div class="matter-section-heading">Statement Date</div>
                    <div class="matter-value matter-value-secondary" id="pStatementDate"></div>
                </div>
            </div>
        </section>

        <div class="section-label">
            <span>Services Performed</span>
            <span class="fixed-badge">Fixed-Fee Full Service</span>
        </div>

        <table class="service-table">
            <thead>
                <tr>
                    <th class="col-date">Completion Date</th>
                    <th class="col-service">Service Description</th>
                    <th class="col-price">Price</th>
                    <th class="col-gst">GST</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td id="pCompletionDate"></td>
                    <td>
                        <div class="package-title">Full Professional Service - Fixed Fee (incl. GST)</div>
                        <div class="package-sub">The charge applies to the complete service package described below, is inclusive of GST, and is not calculated on an hourly basis.</div>
                        <ul class="service-list">
                            <li>Eligibility assessment, including consideration of the applicable law and policy and their application to the client's circumstances and goals.</li>
                            <li>Advice and assistance regarding information and documents required to support the matter at the relevant stage.</li>
                            <li>Preparation of the matter, including necessary forms, documents and supporting submissions.</li>
                            <li>Compilation and finalisation of the matter in the required form for submission or lodgement.</li>
                            <li>Submitting or lodging the matter with the relevant authority for processing and, where applicable, providing post-submission assistance with further information or documents received up to the Completion Date.</li>
                        </ul>
                    </td>
                    <td class="num" id="pPrice">$0.00</td>
                    <td class="num" id="pGst">$0.00</td>
                    <td class="num" id="pServiceTotal">$0.00</td>
                </tr>
            </tbody>
        </table>

        <div class="finance-layout">
            <div class="ledger">
                <div class="ledger-heading">Financial Summary</div>

                <div class="ledger-row strong">
                    <span>Total value of the fixed-fee service performed to date</span>
                    <span id="pTotalValue">$0.00</span>
                </div>

                <div class="ledger-row">
                    <span>Total payment received in client account to date</span>
                    <span id="pPaymentReceived">$0.00</span>
                </div>

                <div class="ledger-row">
                    <span>Total Professional Fee Eligible for Transfer to Operating Account</span>
                    <span id="pOperatingTransferred">$0.00</span>
                </div>

                <div class="ledger-row">
                    <span>Total Departmental fees used for the application</span>
                    <span id="pDepartmentFees">$0.00</span>
                </div>

                <div class="ledger-row">
                    <span>Other Payments made to Authorities / Third Parties on behalf of Client*</span>
                    <span id="pAuthorityPayments">$0.00</span>
                </div>

                <div class="ledger-row">
                    <span>Current balance held in client account</span>
                    <span id="pClientAccountBalance">$0.00</span>
                </div>

                <div class="ledger-row strong">
                    <span>Total fixed professional fee for the service provided (incl. GST)</span>
                    <span id="pProfessionalFee">$0.00</span>
                </div>
            </div>

            <div class="position-card" id="positionCard">
                <div class="position-head">Final Account Position</div>

                <div class="position-body">
                    <div class="position-label" id="positionLabel">Account Settled</div>
                    <div class="position-amount" id="positionAmount">$0.00</div>
                    <div class="position-note" id="positionNote"></div>
                </div>
            </div>
        </div>


        <div class="authority-legend">
            <strong>* Other payments made to Authorities / Third Parties:</strong>
            exclude Departmental fees separately identified above and may include, but are not limited to,
            amounts paid on the client's behalf to review authorities, skills assessing authorities, health examination providers,
            police or character-certificate authorities, and other government or third-party bodies, where the payment is authorised under the Service Agreement.
        </div>

        <div class="legal-note">
            This document is not a Tax Invoice. It is a Statement of Services for the fixed-fee service described above.
            Invoices and receipts are issued separately.
        </div>
    </div>

    <div class="footer">
        <span><strong><?= h(strtoupper($company['name'])) ?></strong> · Statement of Services</span>
        <span>Page 1 of 1</span>
    </div>
</section>
</body>
</html>
