# MehrSoft MehrAccWs Integration Notes

This project may need to exchange warranty activation data with MehrSoft accounting / after-sales software.

Source document in this repository:

- `ForGPT/MehrAccWs Web Service.htm`

Official service URLs found in the source document:

- Service page: `https://www.mehrsofts.com/webservice/mehraccws.asmx`
- WSDL: `https://www.mehrsofts.com/webservice/mehraccws.asmx?WSDL`
- DISCO: `https://www.mehrsofts.com/webservice/mehraccws.asmx?disco`

## Service type

MehrSoft exposes an ASP.NET ASMX web service. Treat it as a SOAP service first. The operation pages also document JSON strings passed as operation parameters, commonly named `jsonParams`.

Typical call shape:

```text
Login(...) -> sessionId
Call operation(sessionId, jsonParams)
Logout(sessionId)
```

Store the service base URL and credentials in environment variables only.

## Suggested Laravel environment keys

```env
MEHRSOFT_BASE_URL=https://www.mehrsofts.com/webservice/mehraccws.asmx
MEHRSOFT_WSDL_URL=https://www.mehrsofts.com/webservice/mehraccws.asmx?WSDL
MEHRSOFT_USERNAME=
MEHRSOFT_PASSWORD=
MEHRSOFT_FINANCIAL_UNIT_CODE=
MEHRSOFT_TIMEOUT_SECONDS=20
```

## Authentication / session

Relevant operations:

- `GetFinancialUnits`: get financial unit codes. Needed before login if the financial unit code is not known.
- `Login`: login and get `sessionId`.
- `Logout`: end the session.
- `App_Login`: login for the online orders user flow; likely not needed for accounting integration unless MehrSoft/Browatt specifically chooses it.

`Login` parameters documented in the source:

```text
fuCode: financial unit code
userName: accounting username
password: accounting password
```

The returned `sessionId` is required by most operations.

## Operations most relevant to warranty activation

### AfterSales_GetProductStatusBySerial

Purpose: check product status by serial number.

Parameters:

```text
sessionId: session id from Login
jsonParams: JSON string
```

Example `jsonParams`:

```json
{
  "Serial": ""
}
```

Fields:

- `Serial`: product serial number.

Documented output shape:

```json
{
  "AfterSaleService": [],
  "ProductDetails": [],
  "ProductInfo": []
}
```

Meaning:

- `AfterSaleService`: warranty / after-sales information when product has already been installed or activated.
- `ProductDetails`: final product attributes if registered during production.
- `ProductInfo`: product code, name, and production date when product is not installed and final attributes are not available.

Suggested use in Browatt:

```text
Before activating warranty:
1. Call AfterSales_GetProductStatusBySerial with the customer-entered serial.
2. If existing AfterSaleService is returned, treat the serial as already activated unless MehrSoft business rules say otherwise.
3. If ProductInfo/ProductDetails exists and no active warranty exists, allow activation.
4. Store MehrSoft response snapshot locally for audit/debugging.
```

### AfterSales_GetWarrantyMonths

Purpose: get warranty period in months.

Example `jsonParams`:

```json
{
  "WarrantyType": "",
  "GoodFullCode": ""
}
```

Fields:

- `WarrantyType`: warranty type code. The source document gives examples: `1` normal, `2` bronze, `3` silver, `4` gold.
- `GoodFullCode`: product full code.

Suggested use:

```text
Use this when the warranty duration should come from MehrSoft instead of local product settings.
```

### AfterSales_GetWarrantySettings

Purpose: get warranty settings. The source document lists the operation but the local HTML overview does not show enough detail in the index snippet.

Suggested use:

```text
Use this during implementation discovery to decide whether warranty types/durations should be synced from MehrSoft.
```

### AfterSales_Save

Purpose: create or update after-sales entities. This is the likely operation for sending warranty activation / installation data to MehrSoft.

Important top-level fields from the source:

```json
{
  "ActionType": "Insert",
  "EntityType": "StrAfterSalesServices",
  "No": "",
  "FixNo": "",
  "Date": "",
  "Time": "",
  "Desc": "",
  "Flag": "",
  "Kol": "",
  "Moein": "",
  "Tafsil": "",
  "Tafsil2": "",
  "Tafsil3": "",
  "TypeTitle": "",
  "AfterSales": {},
  "Details": []
}
```

Top-level fields:

- `ActionType`: `Insert` or `Update`.
- `EntityType`:
  - `StrAssRequest`: after-sales service request.
  - `StrAfterSalesServices`: after-sales service record.
  - `StrAssMaterialRequest`: technician material/part request.
- `No`: document number. Send empty or zero if MehrSoft should assign it.
- `FixNo`: fixed document number.
- `Date`: Jalali date as `yyyyMMdd`, example `13950306`.
- `Time`: time as `HHmmss`, example `142501`.
- `Desc`: document description.
- `Flag`: document state, `0` unconfirmed, `1` confirmed.
- `Kol`, `Moein`, `Tafsil`, `Tafsil2`, `Tafsil3`: technician/accounting account codes.
- `TypeTitle`: configured type title for the selected entity.
- `AfterSales`: after-sales product/customer data.
- `Details`: document detail rows.

`AfterSales` fields documented in the source:

```json
{
  "GoodSerial": "",
  "GoodFullCode": "",
  "ProductionDate": "",
  "WarrantyType": "",
  "WarrantyPeriodMonth": "",
  "CustType": "",
  "CustSex": "",
  "CustFirstName": "",
  "CustLastName": "",
  "CustName": "",
  "CustNationalCode": "",
  "CustState": "",
  "CustCity": "",
  "CustAddress": "",
  "CustPhone": "",
  "AfterSalesDesc": ""
}
```

`AfterSales` fields:

- `GoodSerial`: product serial.
- `GoodFullCode`: product full code.
- `ProductionDate`: production date.
- `WarrantyType`: product warranty type.
- `WarrantyPeriodMonth`: warranty period in months.
- `CustType`: customer type, `0` individual and `1` legal/company.
- `CustSex`: `0` female and `1` male.
- `CustFirstName`: customer first name.
- `CustLastName`: customer last name.
- `CustName`: company/organization name when customer is legal.
- `CustNationalCode`: national code or legal national id.
- `CustState`: state/province code.
- `CustCity`: city code.
- `CustAddress`: customer address.
- `CustPhone`: customer phone/mobile.
- `AfterSalesDesc`: after-sales description.

`Details` row fields documented in the source:

```json
{
  "StdGsId": "",
  "GoodFullCode": "",
  "StdCount": "",
  "StdPrice": "",
  "StdPaidWithCustomer": "",
  "StdDesc": "",
  "StdRequestStNo": ""
}
```

`Details` fields:

- `StdGsId`: warehouse/store code.
- `GoodFullCode`: goods/services/material code.
- `StdCount`: quantity.
- `StdPrice`: unit price without separators, example `1250000`.
- `StdPaidWithCustomer`: paid by customer, `0` no and `1` yes.
- `StdDesc`: row description.
- `StdRequestStNo`: previous after-sales request number if saving service from an existing request; otherwise empty.

Suggested use in Browatt warranty activation:

```text
When customer activates warranty:
1. Validate serial locally.
2. Call AfterSales_GetProductStatusBySerial.
3. Build an AfterSales_Save payload with ActionType=Insert and EntityType=StrAfterSalesServices.
4. Send customer, product, warranty type, and warranty period.
5. Save local warranty record with MehrSoft request/response metadata.
6. If MehrSoft call fails, keep local activation in a pending-sync state and retry from a queue.
```

## Other useful after-sales operations

- `AfterSales_GetAssRequest`: get after-sales service requests not yet converted into after-sales service documents.
- `AfterSales_GetServiceHistory`: get completed after-sales service history.
- `AfterSales_GetTechnicianAccountStatement`: technician account statement.

These are probably later-phase features for repair/service workflows, not required for the first warranty activation release.

## Location/account helper operations

Potentially useful for mapping customer address fields:

- `GetAccCities`
- `GetAccLocations`

Potentially useful for account/customer mapping:

- `FindTafsilAccountBy`
- `FindTafsilAccountByKolAndMoeinCode`
- `FindTafsilAccountLimitFieldBy`
- `SaveTafsilAccount`
- `SaveTafsilAccountL4`

Do not use these until Browatt/MehrSoft clarifies whether customers must be created as accounting tafsil accounts during warranty activation.

## Suggested Laravel abstraction

Keep MehrSoft behind an application service.

```php
interface MehrSoftClient
{
    public function getProductStatusBySerial(string $serial): array;

    public function getWarrantyMonths(string $warrantyType, string $goodFullCode): ?int;

    public function saveAfterSales(array $payload): array;
}
```

Suggested implementation:

```php
final class SoapMehrSoftClient implements MehrSoftClient
{
    // Uses SoapClient against MEHRSOFT_WSDL_URL.
}
```

## Local persistence recommendations

Keep local records even when MehrSoft is the system of record.

Suggested columns for warranty activation sync:

```text
warranties
- id
- user_id
- product_serial
- product_code nullable
- warranty_type nullable
- warranty_period_months nullable
- activated_at
- starts_at nullable
- expires_at nullable
- mehrsoft_sync_status: pending, synced, failed
- mehrsoft_synced_at nullable
- mehrsoft_document_no nullable
- mehrsoft_fix_no nullable
- mehrsoft_last_error nullable
- created_at
- updated_at
```

Optional audit table:

```text
mehrsoft_api_logs
- id
- operation
- request_payload_json
- response_payload_json nullable
- success
- error_message nullable
- duration_ms nullable
- created_at
```

Mask sensitive customer data in logs if logs may be exposed to developers/support staff.

## Open questions for Browatt / MehrSoft

- Which MehrSoft operation is officially required for warranty activation: `AfterSales_Save` with `StrAfterSalesServices`, or another entity/type?
- Should the warranty activation be saved with `Flag=0` or `Flag=1`?
- What exact `TypeTitle`, account codes (`Kol`, `Moein`, `Tafsil`), warranty types, warehouse codes, and detail rows should be sent?
- Should customer records be created/updated as tafsil accounts, or only embedded inside `AfterSales`?
- Which date should be used as warranty start: customer activation date, purchase date, installation date, or production date?
- Should activation be rejected if MehrSoft is unavailable, or accepted locally as pending sync?
- What should happen if MehrSoft reports an existing `AfterSaleService` for the serial?

