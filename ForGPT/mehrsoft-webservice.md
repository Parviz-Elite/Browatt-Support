# MehrSoft MehrAccWs Web Service Reference

This file is generated from `ForGPT/MehrAccWs Web Service.htm` and keeps the full method list, method links, documented inputs, JSON samples, and notes from the ASMX service page in Markdown form.

## Service URLs

- Service page: `https://www.mehrsofts.com/webservice/mehraccws.asmx`
- WSDL: `https://www.mehrsofts.com/webservice/mehraccws.asmx?WSDL`
- DISCO: `https://www.mehrsofts.com/webservice/mehraccws.asmx?disco`

## Integration Notes

- The service is an ASP.NET ASMX web service. Use the WSDL/SOAP contract as the source of truth for exact request and response envelopes.
- Most business methods require `sessionId`, which is received from `Login` or `App_Login`.
- Several methods accept `jsonParams`; the documented value is a JSON string, not necessarily a native JSON HTTP body.
- Dates in examples are Jalali dates formatted as `yyyyMMdd`, for example `13950306`. Times are commonly formatted as `HHmmss`, for example `142501`.
- Keep credentials, financial unit code, and service URLs in environment variables.

Suggested environment keys:

```env
MEHRSOFT_BASE_URL=https://www.mehrsofts.com/webservice/mehraccws.asmx
MEHRSOFT_WSDL_URL=https://www.mehrsofts.com/webservice/mehraccws.asmx?WSDL
MEHRSOFT_USERNAME=
MEHRSOFT_PASSWORD=
MEHRSOFT_FINANCIAL_UNIT_CODE=
MEHRSOFT_TIMEOUT_SECONDS=20
```

## Method Index

- [AfterSales_GetAssRequest](#aftersales_getassrequest)
- [AfterSales_GetProductStatusBySerial](#aftersales_getproductstatusbyserial)
- [AfterSales_GetServiceHistory](#aftersales_getservicehistory)
- [AfterSales_GetTechnicianAccountStatement](#aftersales_gettechnicianaccountstatement)
- [AfterSales_GetWarrantyMonths](#aftersales_getwarrantymonths)
- [AfterSales_GetWarrantySettings](#aftersales_getwarrantysettings)
- [AfterSales_Save](#aftersales_save)
- [App_GetServerInfo](#app_getserverinfo)
- [App_Login](#app_login)
- [ChangeTafsilCode](#changetafsilcode)
- [DeleteSegmentByCode](#deletesegmentbycode)
- [DeleteTafsilAccount](#deletetafsilaccount)
- [FindTafsilAccountBy](#findtafsilaccountby)
- [FindTafsilAccountByKolAndMoeinCode](#findtafsilaccountbykolandmoeincode)
- [FindTafsilAccountLimitFieldBy](#findtafsilaccountlimitfieldby)
- [GetAccCities](#getacccities)
- [GetAccLocations](#getacclocations)
- [GetAccountByCodeAndLevel](#getaccountbycodeandlevel)
- [GetAccountReport](#getaccountreport)
- [GetAllAccounts](#getallaccounts)
- [GetCustomerStatus](#getcustomerstatus)
- [GetFinancialUnits](#getfinancialunits)
- [GetGoodsCountByStoreWithQcAndSalePrice](#getgoodscountbystorewithqcandsaleprice)
- [GetKolAccounts](#getkolaccounts)
- [GetMoeinAccounts](#getmoeinaccounts)
- [GetSegmentByCode](#getsegmentbycode)
- [GetStockAccNameByStNo](#getstockaccnamebystno)
- [GetUserGroupAndUserList](#getusergroupanduserlist)
- [Login](#login)
- [Logout](#logout)
- [ManualVoucherChangeState](#manualvoucherchangestate)
- [ManualVoucherDelete](#manualvoucherdelete)
- [ManualVoucherGetByFixNo](#manualvouchergetbyfixno)
- [ManualVoucherGetNewFixNo](#manualvouchergetnewfixno)
- [ManualVoucherInsert](#manualvoucherinsert)
- [ManualVoucherUpdate](#manualvoucherupdate)
- [PdaStockInsert](#pdastockinsert)
- [RpRuleGetByNo](#rprulegetbyno)
- [RpRuleGetNewNo](#rprulegetnewno)
- [RpRuleSave](#rprulesave)
- [SaveOutSerials](#saveoutserials)
- [SaveSegment](#savesegment)
- [SaveTafsilAccount](#savetafsilaccount)
- [SaveTafsilAccountL4](#savetafsilaccountl4)
- [StockCountGetGood](#stockcountgetgood)
- [StockCountGetList](#stockcountgetlist)
- [StockCountSave](#stockcountsave)
- [StockDeleteByFixNo](#stockdeletebyfixno)
- [StockDeleteByNo](#stockdeletebyno)
- [StockExistByFixNo](#stockexistbyfixno)
- [StockFindGoodBy](#stockfindgoodby)
- [StockGetBasicInfo](#stockgetbasicinfo)
- [StockGetByNo](#stockgetbyno)
- [StockGetFactorReport](#stockgetfactorreport)
- [StockGetGoodBy](#stockgetgoodby)
- [StockGetGoodCount](#stockgetgoodcount)
- [StockGetGoodCountWithQc](#stockgetgoodcountwithqc)
- [StockGetGoodReport](#stockgetgoodreport)
- [StockGetGoods](#stockgetgoods)
- [StockGetNewFixNo](#stockgetnewfixno)
- [StockGetOrderControlReport](#stockgetordercontrolreport)
- [StockGoodSave](#stockgoodsave)
- [StockGoodSaveV2](#stockgoodsavev2)
- [StockPrintAsPdf](#stockprintaspdf)
- [StockSave](#stocksave)
- [StockSaveV2](#stocksavev2)
- [SysPosDoPayment](#sysposdopayment)
- [SysPosGetList](#sysposgetlist)
- [TreasuryDeleteByFixNo](#treasurydeletebyfixno)
- [TreasuryDeleteByNo](#treasurydeletebyno)
- [TreasuryExistByFixNo](#treasuryexistbyfixno)
- [TreasuryGetChecks](#treasurygetchecks)
- [TreasuryGetNewFixNo](#treasurygetnewfixno)
- [TreasurySave](#treasurysave)
- [VisitorLocationSave](#visitorlocationsave)
- [VisitorLocationSearch](#visitorlocationsearch)

## Methods

### AfterSales_GetAssRequest

- Operation page: [AfterSales_GetAssRequest](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_GetAssRequest)

**دریافت لیست درخواست‌های خدمات پس از فروش که هنوز به برگه خدمات پس از فروش تبدیل نشده‌اند و به سرویس‌کار ارجاع شده‌اند**

- **sessionId:** شناسه فعالیت دریافت‌شده از متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل پارامترهای ورودی. نمونه:

```json
{
  "TafsilId": "",
  "State": ""
}
```

**توضیحات پارامترهای JSON:**

- **TafsilId:** شناسه حساب تفصیلی سرویس‌کار

- **State:** وضعیت برگه

- خالی: تمامی درخواست‌های تاییدشده و تاییدنشده

- 0: فقط درخواست‌های تاییدنشده

- 1: فقط درخواست‌های تاییدشده

### AfterSales_GetProductStatusBySerial

- Operation page: [AfterSales_GetProductStatusBySerial](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_GetProductStatusBySerial)

**استعلام وضعیت محصول براساس سریال**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "Serial": ""
}
```

**توضیحات کامل پارامترهای JSON:**

- **Serial:**شماره سریال محصول

---

**ساختار خروجی (JSON):**

```json
{
 "AfterSaleService": [ { (اطلاعات گارانتی زمانی  وجود خواهد داشت که محصول قبلا نصب و راه اندازی شده باشد) اطلاعات گارانتی } ],
 "ProductDetails": [ { (در صورتی که ویژگی های محصول به هنگام تولید ثبت شده باشد) اطلاعات ویژگی های نهایی محصول ],
 "ProductInfo": [ { (در صورتی که محصول نصب نشده و ویژگی های نهایی محصول وجود نداشته باشد اطلاعات محصول برگشت داده خواهد شد) کد، نام و تاریخ تولید محصول } ]
}
```

### AfterSales_GetServiceHistory

- Operation page: [AfterSales_GetServiceHistory](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_GetServiceHistory)

**دریافت فهرست خدمات پس از فروش انجام‌شده**

- **sessionId:** شناسه نشست دریافت‌شده از متد Login

- **jsonParams:** رشته JSON شامل پارامترهای ورودی. نمونه:

```json
{
  "TafsilId": "",
  "DateFrom": "",
  "DateTo": "",
  "GoodFullCode": "",
  "ProductSerial": "",
  "ProductId": "",
  "ProductInstallationDateFrom": "",
  "ProductInstallationDateTo": "",
  "CustomerName": "",
  "CustomerNationalCode": ""
}
```

**توضیحات پارامترهای JSON:**

- **TafsilId:** شناسه حساب تفصیلی سرویس‌کار

- **DateFrom:** تاریخ ارائه خدمات از

- **DateTo:** تاریخ ارائه خدمات تا

- **GoodFullCode:** کد خدمات یا مواد مصرفی

- **ProductSerial:** شماره سریال محصول

- **ProductId:** شناسه محصول

- **ProductInstallationDateFrom:** تاریخ نصب محصول از

- **ProductInstallationDateTo:** تاریخ نصب محصول تا

- **CustomerName:** نام خریدار

- **CustomerNationalCode:** کد ملی خریدار

### AfterSales_GetTechnicianAccountStatement

- Operation page: [AfterSales_GetTechnicianAccountStatement](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_GetTechnicianAccountStatement)

**گرفتن گردش مالی حساب سرویس‌کار**

- **sessionId:** شناسه فعالیت دریافت‌شده از متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل پارامترهای ورودی. نمونه:

```json
{
  "TafsilId": "",
  "DateFrom": "",
  "DateTo": "",
  "MoeinId": "",
  "GsIds": ""
}
```

**توضیحات پارامترهای JSON:**

- **TafsilId:** شناسه حساب تفصیلی سرویس‌کار

- **DateFrom:** تاریخ صورتحساب از

- **DateTo:** تاریخ صورتحساب تا

- **MoeinId:**
شناسه حساب معین مرتبط با حساب سرویس کار، در صورتی که بخواهید گردش های
مالی سرویس کار در تمامی معین ها نمایش داده شود خالی ارسال گردد

- **GsIds:** انبار سرویس کار، جهت نمایش گردش کالایی به همراه نمایش مانده کالاها

### AfterSales_GetWarrantyMonths

- Operation page: [AfterSales_GetWarrantyMonths](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_GetWarrantyMonths)

**استعلام مدت گارانتی کالا**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "WarrantyType": "",
  "GoodFullCode": ""
}
```

**توضیحات کامل پارامترهای JSON:**

- **WarrantyType:**کد نوع گارانتی، برای مثال 1 = گارانتی (نرمال)، 2=گارانتی (برنزی)، 3=گارانتی (نقره ای)، 4=گارانتی (طلایی)

- **GoodFullCode:**کد کالای محصول

### AfterSales_GetWarrantySettings

- Operation page: [AfterSales_GetWarrantySettings](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_GetWarrantySettings)

**گرفتن عناوین نوع گارانتی ها به همراه مدت گارانتی ماه به صورت لیست که شامل کالا، گروه کالایی و یا به صورت کلی می باشد**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

### AfterSales_Save

- Operation page: [AfterSales_Save](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=AfterSales_Save)

**ذخیره برگه های خدمات پس از فروش**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل اطلاعات برگه

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
  "AfterSales": {
    "GoodSerial": "",
    "GoodFullCode": "",
    "ProductionDate": "",
    "WarrantyType": "",
    "WarrantyPeriodMonth": "",
    "CustType": "0",
    "CustSex": "1",
    "CustFirstName": "",
    "CustLastName": "",
    "CustName": "",
    "CustNationalCode": "",
    "CustState": "",
    "CustCity": "",
    "CustAddress": "",
    "CustPhone": "",
    "AfterSalesDesc": ""
  },
  "Details": [
    {
      "StdGsId": "",
      "GoodFullCode": "",
      "StdCount": "",
      "StdPrice": "",
      "StdPaidWithCustomer": "",
      "StdDesc": "",
      "StdRequestStNo": ""
    }
  ]
}
```

**توضیحات پارامترهای JSON:**

- **ActionType:** نوع فعالیت

- Insert (ایجاد)

- Update (ویرایش)

- **EntityType:** نوع موجودیت

- StrAssRequest (درخواست خدمات پس از فروش)

- StrAfterSalesServices (خدمات پس از فروش)

- StrAssMaterialRequest (درخواست قطعه (سرویس کار))

- **No:** شماره برگه (در صورتی که بخواهید شماره توسط سیستم اختصاص داده شود خالی یا صفر ارسال شود)

- **FixNo:** شماره ثابت برگه

- **Date:** تاریخ، مثال 13950306

- **Time:** ساعت، مثال 142501

- **Desc:** شرح برگه

- **Flag:** وضعیت برگه -> 0 (تایید نشده) و 1 (تایید شده)

- **Kol:** کد حساب سرویس کار کل

- **Moein:** کد حساب سرویس کار معین

- **Tafsil:** کد حساب سرویس کار تفصیلی

- **Tafsil2:** کد حساب سرویس کار تفصیلی 2

- **Tafsil3:** کد حساب سرویس کار تفصیلی 3

- **TypeTitle:** نوع (عنوان نوع های تعریف شده برای موجودیت خواسته شده)

- **AfterSales:** اطلاعات خدمات پس از فروش (در فرم درخواست قطعه نیازی به ارسال اطلاعات فوق نمی باشد)

- **GoodSerial:** شماره سریال محصول

- **GoodFullCode:** کد کالای محصول

- **ProductionDate:** تاریخ تولید محصول

- **WarrantyType:** نوع گارانتی محصول

- **WarrantyPeriodMonth:** مدت گارانتی به ماه

- **CustType:** نوع مشتری (0=حقیقی ، 1=حقوقی)

- **CustSex:** جنسیت (0=خانم ، 1=آقا)

- **CustFirstName:** نام مشتری

- **CustLastName:** نام خانوادگی مشتری

- **CustName:** نام شرکت یا سازمان در صورت حقوقی بودن

- **CustNationalCode:** کد ملی یا شناسه ملی

- **CustState:** کد استان

- **CustCity:** کد شهر

- **CustAddress:** آدرس مشتری

- **CustPhone:** تلفن تماس مشتری

- **AfterSalesDesc:** توضیحات خدمات پس از فروش

- **Details:** لیست ردیف های برگه

- **StdGsId:** کد انبار

- **GoodFullCode:** کد کالا

- **StdCount:** تعداد

- **StdPrice:** قیمت واحد، بدون جداکننده مبلغ. مثال: 1250000

- **StdPaidWithCustomer:** پرداخت شده توسط مشتری (0=خیر ، 1=بله)

- **StdDesc:** شرح ردیف

- **StdRequestStNo:**شماره درخواست در صورت ثبت خدمات پس از فروش از روی درخواست خدمات پس از فروش قبلی ثبت شده، در غیر این صورت خالی ارسال شود

### App_GetServerInfo

- Operation page: [App_GetServerInfo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=App_GetServerInfo)

اطلاعات سرور مشتری

- **customerSerial:**شماره سریال مشتری (قابل نمایش در نرم افزار مشتری)

### App_Login

- Operation page: [App_Login](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=App_Login)

جهت ورود به سیستم و گرفتن شناسه فعالیت ( کاربر سیستم سفارشات )
fuCode : کد سال مالی
userCellphone : (شماره همراه) نام کاربری
password : رمز کاربری
confirmCode : کد تایید ورود (ارسال شده از طریق اس ام اس) در صورت فعال بودن تنظمات ورود دو مرحله ای
لازم به توضیح می باشد که برای دریافت نام و رمز کاربری باید از سیستم سفارشات آنلاین اقدام نمایید
کد سال مالی را می توانید با استفاده از تابع GetFinancialUnits بدست آورید

### ChangeTafsilCode

- Operation page: [ChangeTafsilCode](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ChangeTafsilCode)

تعویص کد حساب تفصیلی
sessionId : شناسه فعالیت گرفته شده در متد Login
oldTgCode : کد سرگروه تفصیلی نیاز به تعویض کد
oldTafsilCode : کد حساب تفصیلی خواسته شده جهت تعویض کد
newTgCode : کد سرگروه تفصیلی که می خواهید کد جدید به آن انتقال داده شود
newTafsilCode : کد تفصیلی که می خواهید با کد قبلی تعویض گردد

### DeleteSegmentByCode

- Operation page: [DeleteSegmentByCode](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=DeleteSegmentByCode)

حذف مرکز بر اساس کد مرکز
sessionId : شناسه فعالیت گرفته شده در متد Login
segmentCode : کد مرکز

### DeleteTafsilAccount

- Operation page: [DeleteTafsilAccount](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=DeleteTafsilAccount)

حذف حساب تفصیلی
sessionId : شناسه فعالیت گرفته شده در متد Login
accTgCode : کد سرگروه تفصیلی
accTafsilCode : کد حساب تفصیلی
accTafsil2Code : کد حساب تفصیلی 2 (در صورت حساب تفصیلی سطح یک خالی ارسال شود)
accTafsil2Code : کد حساب تفصیلی 2 (در صورت حساب تفصیلی سطح دو خالی ارسال شود)
removeChildAccounts : به همراه حذف زیرحسابها (در صورت داشتن زیر حساب و نیاز به حذف حساب خواسته شده باید مقدار true ارسال شود)

### FindTafsilAccountBy

- Operation page: [FindTafsilAccountBy](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=FindTafsilAccountBy)

جستجوی حسابهای تفصیلی
sessionId : شناسه فعالیت گرفته شده در متد Login
accDNationalCode : کد ملی
accDEconomicNo : کد اقتصادی
accDPersonCode : کد پرسنلی
accName : نام
accTgCode : کد سرگروه تفصیلی

### FindTafsilAccountByKolAndMoeinCode

- Operation page: [FindTafsilAccountByKolAndMoeinCode](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=FindTafsilAccountByKolAndMoeinCode)

جستجوی حسابهای تفصیلی تخصیص داده شده برای کل و معین خاص
sessionId : شناسه فعالیت گرفته شده در متد Login
kolCode : کد کل
moeinCode : کد معین
accDNationalCode : کد ملی
accName : نام

### FindTafsilAccountLimitFieldBy

- Operation page: [FindTafsilAccountLimitFieldBy](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=FindTafsilAccountLimitFieldBy)

جستجوی حسابهای تفصیلی - نتیجه جستجو فیلدهای محدود کد تفصیلی، نام تفصیلی، کد ملی، کد پرسنلی، شماره و شناسه حساب تفصیبلی
sessionId : شناسه فعالیت گرفته شده در متد Login
accDNationalCode : کد ملی
accDPersonCode : کد پرسنلی
accName : نام
accTgCode : کدهای سرگروه تفصیلی - در صورت درخواست بیش از یک سرگروه تفصیلی با کاما از هم جدا شوند

### GetAccCities

- Operation page: [GetAccCities](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetAccCities)

فهرست تمامی استانها و شهرستان ها در سیستم به صورت پدر فرزندی
sessionId : شناسه فعالیت گرفته شده در متد Login

### GetAccLocations

- Operation page: [GetAccLocations](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetAccLocations)

فهرست تمامی موقعیت های جغرافیایی تعریف شده در سیستم به صورت پدر فرزندی
sessionId : شناسه فعالیت گرفته شده در متد Login

### GetAccountByCodeAndLevel

- Operation page: [GetAccountByCodeAndLevel](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetAccountByCodeAndLevel)

گرفتن حساب بر اساس کد حساب و سطح حساب
sessionId : شناسه فعالیت گرفته شده در متد Login
accCode : کد حساب
accLevel : سطح حساب

### GetAccountReport

- Operation page: [GetAccountReport](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetAccountReport)

گرفتن گزارش گردش حساب
sessionId : شناسه فعالیت گرفته شده در متد Login
accKol : کد حساب کل
accMoein : کد حساب معین
accTafsil : کد حساب تفصیلی
accTafsil2 : کد حساب تفصیلی 2
accTafsil3 : کد حساب تفصیلی 3

### GetAllAccounts

- Operation page: [GetAllAccounts](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetAllAccounts)

فهرست تمامی حسابهای تعریف شده در سیستم
sessionId : شناسه فعالیت گرفته شده در متد Login

### GetCustomerStatus

- Operation page: [GetCustomerStatus](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetCustomerStatus)

گرفتن وضعیت مالی حساب
sessionId : شناسه فعالیت گرفته شده در متد Login
accKol : کد حساب کل
accMoein : کد حساب معین
accTafsil : کد حساب تفصیلی
accTafsil2 : کد حساب تفصیلی 2
accTafsil3 : کد حساب تفصیلی 3
date : وضعیت حساب تا تاریخ، مثال 13950306
time : ساعت، مثال 130120

### GetFinancialUnits

- Operation page: [GetFinancialUnits](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetFinancialUnits)

لیست سال های مالی تعریف شده و قابل رویت در سیستم

### GetGoodsCountByStoreWithQcAndSalePrice

- Operation page: [GetGoodsCountByStoreWithQcAndSalePrice](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetGoodsCountByStoreWithQcAndSalePrice)

گرفتن موجودی کالا
sessionId : شناسه فعالیت گرفته شده در متد Login
goodStoreIds
: شناسه انبارها [در صورت چندین انبار شناسه انبارها با کارکتر (,) از هم
جدا شوند برای مثال 1,2 و در صورت درخواست موجودی تمامی انبارها خالی ارسال
شود]
goodIds : شناسه کالاها [در صورت چندین کالا شناسه کالاها با
کارکتر (,) از هم جدا شوند برای مثال 1,2 و در صورت درخواست موجودی تمامی
کالاها خالی ارسال شود]
toDate : تا تاریخ [مثال 13990720 در صورتی که بخواهید محدودیت تاریخ اعمال نشود عدد صفر را ارسال کنید]
toTime : تا ساعت [مثال 132005 در صورتی که بخواهید محدودیت تاریخ اعمال نشود عدد صفر را ارسال کنید]

### GetKolAccounts

- Operation page: [GetKolAccounts](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetKolAccounts)

فهرست حسابهای کل تعریف شده در سیستم
sessionId : شناسه فعالیت گرفته شده در متد Login

### GetMoeinAccounts

- Operation page: [GetMoeinAccounts](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetMoeinAccounts)

فهرست حسابهای معین تعریف شده برای کل خواسته شده در سیستم
sessionId : شناسه فعالیت گرفته شده در متد Login
kolCode : کد حساب کل

### GetSegmentByCode

- Operation page: [GetSegmentByCode](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetSegmentByCode)

گرفتن مرکز بر اساس کد مرکز
sessionId : شناسه فعالیت گرفته شده در متد Login
segmentCode : کد مرکز

### GetStockAccNameByStNo

- Operation page: [GetStockAccNameByStNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetStockAccNameByStNo)

گرفتن شناسه برگه بر اساس شماره
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع برگه
stNo : شماره

### GetUserGroupAndUserList

- Operation page: [GetUserGroupAndUserList](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=GetUserGroupAndUserList)

گرفتن لیست گروه های کاربران و لیست کاربران
sessionId : شناسه فعالیت گرفته شده در متد Login

### Login

- Operation page: [Login](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=Login)

جهت ورود به سیستم و گرفتن شناسه فعالیت
fuCode : کد سال مالی
userName : نام کاربری
password : رمز کاربری
لازم به توضیح می باشد که برای دریافت نام و رمز کاربری باید از سیستم حسابداری اقدام نمایید
کد سال مالی را می توانید با استفاده از تابع GetFinancialUnits بدست آورید

### Logout

- Operation page: [Logout](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=Logout)

جهت خروج کاربر از سیستم
sessionId : شناسه فعالیت گرفته شده در متد Login

### ManualVoucherChangeState

- Operation page: [ManualVoucherChangeState](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ManualVoucherChangeState)

تغییر وضعیت سند دستی / قرارداد
sessionId : شناسه فعالیت گرفته شده در متد Login
mvFixNo : شماره ثابت
mvFlag : وضعیت 0 : تایید نشده 1 : تایید شده

### ManualVoucherDelete

- Operation page: [ManualVoucherDelete](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ManualVoucherDelete)

حذف سند دستی / قرارداد
sessionId : شناسه فعالیت گرفته شده در متد Login
mvFixNo : شماره ثابت، ویرایش سند متناظر با شماره ثابت فوق

### ManualVoucherGetByFixNo

- Operation page: [ManualVoucherGetByFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ManualVoucherGetByFixNo)

گرفتن اطلاعات سند دستی / قرارداد
sessionId : شناسه فعالیت گرفته شده در متد Login
mvFixNo : شماره ثابت

### ManualVoucherGetNewFixNo

- Operation page: [ManualVoucherGetNewFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ManualVoucherGetNewFixNo)

گرفتن شماره ثابت جدید
sessionId : شناسه فعالیت گرفته شده در متد Login

### ManualVoucherInsert

- Operation page: [ManualVoucherInsert](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ManualVoucherInsert)

**ذخیره سند دستی / قرارداد**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **mvFixNo:**
شماره ثابت جهت ذخیره. این شماره توسط سیستم استفاده‌کننده جهت پیگیری
وضعیت ذخیره در مواقع قطع ارتباط یا عدم پاسخ سرور استفاده می‌شود. **اجباری** و **غیرتکراری** است.

- **mvSegmentCode:** کد مرکز هزینه یا پروژه (شماره قرارداد)

- **mvDate:** تاریخ سند (مثال: 13950306)

- **mvDesc:** شرح سند

- **mvMoeinId:** شناسه حساب معین

- **mvTafsilId:** شناسه حساب تفصیلی

- **mvDetails:** آرایه‌ای از ردیف‌های سند به فرمت زیر:

```json
[
  {
    "MvdCurrencyPrice": "نرخ ارز",
    "MvdCurrencyBes": "مبلغ بستانکاری ارز",
    "MvdCurrencyBed": "مبلغ بدهکاری ارز",
    "MvdCurrencyId": "شناسه نوع ارز",
    "MvdPriceBes": "مبلغ بستانکاری ریالی",
    "MvdPriceBed": "مبلغ بدهکاری ریالی",
    "MvdDesc": "شرح",
    "MvdMoeinId": "شناسه حساب معین",
    "MvdTafsilId": "شناسه حساب تفصیلی",
    "VdKol": "کد حساب کل",
    "VdMoein": "کد حساب معین",
    "VdTafsil": "کد حساب تفصیلی",
    "VdTafsil2": "کد حساب تفصیلی 2",
    "VdTafsil3": "کد حساب تفصیلی 3",
    "MvdTrackingNo": "شماره پیگیری"
  }
]
```

**توضیحات تکمیلی:**

- فرمت ردیف‌ها مطابق نمونه بالا بوده و برای سایر ردیف‌ها تکرار می‌شود.

- در صورت ثبت کد حساب‌ها، نیازی به ثبت شناسه حساب معین و تفصیلی نیست.

- مبالغ باید به‌صورت عددی و بدون جداساز ثبت شوند (مثال: 1250000 ریال)

### ManualVoucherUpdate

- Operation page: [ManualVoucherUpdate](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=ManualVoucherUpdate)

**ویرایش سند دستی / قرارداد**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **mvFixNo:** شماره ثابت سند. برای ویرایش سندی که با این شماره ذخیره شده استفاده می‌شود. **اجباری** و **غیرتکراری** است.

- **mvSegmentCode:** کد مرکز هزینه یا پروژه (شماره قرارداد)

- **mvDate:** تاریخ سند (مثال: 13950306)

- **mvDesc:** شرح سند

- **mvMoeinId:** شناسه حساب معین

- **mvTafsilId:** شناسه حساب تفصیلی

- **mvDetails:** آرایه‌ای از ردیف‌های سند به فرمت زیر:

```json
[
  {
    "MvdCurrencyPrice": "نرخ ارز",
    "MvdCurrencyBes": "مبلغ بستانکاری ارز",
    "MvdCurrencyBed": "مبلغ بدهکاری ارز",
    "MvdCurrencyId": "شناسه نوع ارز",
    "MvdPriceBes": "مبلغ بستانکاری ریالی",
    "MvdPriceBed": "مبلغ بدهکاری ریالی",
    "MvdDesc": "شرح",
    "MvdMoeinId": "شناسه حساب معین",
    "MvdTafsilId": "شناسه حساب تفصیلی",
    "VdKol": "کد حساب کل",
    "VdMoein": "کد حساب معین",
    "VdTafsil": "کد حساب تفصیلی",
    "VdTafsil2": "کد حساب تفصیلی 2",
    "VdTafsil3": "کد حساب تفصیلی 3"
    "MvdTrackingNo": "شماره پیگیری"
  }
]
```

**توضیحات تکمیلی:**

- فرمت ردیف‌ها مطابق نمونه بالا بوده و برای سایر ردیف‌ها تکرار می‌شود.

- در صورت ثبت کد حساب‌ها، نیازی به ثبت شناسه حساب معین و تفصیلی نیست.

- مبالغ باید به‌صورت عددی و بدون جداساز ثبت شوند (مثال: 1250000 ریال)

### PdaStockInsert

- Operation page: [PdaStockInsert](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=PdaStockInsert)

ذخیره رسید / حواله
sessionId : شناسه فعالیت گرفته شده در متد Login
entity : نوع موجودیت سند
details : جزئیات

### RpRuleGetByNo

- Operation page: [RpRuleGetByNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=RpRuleGetByNo)

گرفتن اطلاعات دستور دریافت / پرداخت
sessionId : شناسه فعالیت گرفته شده در متد Login
rpEntityType : نوع موجودیت خواسته شده ، 1 دستور دریافت ، 2 دستور پرداخت
rpNo : شماره

### RpRuleGetNewNo

- Operation page: [RpRuleGetNewNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=RpRuleGetNewNo)

گرفتن شماره جدید برای دستور دریافت / پرداخت
sessionId : شناسه فعالیت گرفته شده در متد Login
rpEntityType : نوع موجودیت خواسته شده ، 1 دستور دریافت ، 2 دستور پرداخت

### RpRuleSave

- Operation page: [RpRuleSave](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=RpRuleSave)

ذخیره دستور دریافت / پرداخت
sessionId : شناسه فعالیت گرفته شده در متد Login
rpEntityType : نوع موجودیت خواسته شده ، 1 دستور دریافت ، 2 دستور پرداخت
rpNo : شماره دستور - در صورتی که خالی یا صفر ارسال شود سیستم به صورت خودکار شماره جدید به دستور انتصاب خواهد داد)
rpSegmentCode : کد مرکز هزینه یا پروژه -(شماره قرارداد)
rpDate : تاریخ، مثال 13950306
rpDesc : شرح
rpMoeinId : شناسه حساب معین
rpTafsilId : شناسه حساب تفصیلی
rpFlag : وضعیت تایید شده دستور -> 0 تایید نشده ، 1 تایید شده
mvDetails
: [{"RpRdType":"نوع دریافت -> 1:نقدی - 2:چک - 3: تخفیف - 4:واگذاری
چک های دریافتی","RpRdPrice":"مبلغ","RpRdDate":"تاریخ
سررسید","RpRdDesc":"شرح"}]
فرمت ردیف های دستور دریافت / پرداخت به شکل بالا بوده و برای سایر ردیف ها تکرار خواهد شد
فرمت مبلغ ها به صورت عدد می باشد و از جداساز مبلغ استفاده نشود، مثال 1250000 ریال
فرمت تاریخ سررسید، مثال 13950306

### SaveOutSerials

- Operation page: [SaveOutSerials](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=SaveOutSerials)

ذخیره سریال های خروجی بر اساس شماره حواله
sessionId : شناسه فعالیت گرفته شده در متد Login
stNo : شماره
dtSerials : لیست سریال های خروجی

### SaveSegment

- Operation page: [SaveSegment](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=SaveSegment)

ایجاد و یا ویرایش مراکز هزینه و پروژه - (شماره قرارداد)
پس از ایجاد و یا ویرایش، در صورت موفق بودن عملیات شناسه مرکز برگشت داده خواهد شد
sessionId : شناسه فعالیت گرفته شده در متد Login
accId : شناسه مرکز
accId : کد مرکز
accName : عنوان مرکز

### SaveTafsilAccount

- Operation page: [SaveTafsilAccount](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=SaveTafsilAccount)

ایجاد و یا ویرایش حساب تفصیلی
پس از ایجاد و یا ویرایش حساب تفصیلی، در صورت موفق بودن عملیات شناسه حساب#کد حساب تفصیلی برگشت داده خواهد شد
sessionId : شناسه فعالیت گرفته شده در متد Login
accParentId : شناسه حساب پدر (در صورت نبودن شناسه حساب پدر عدد صفر ارسال شود)
accId : شناسه حساب (در صورت حساب جدید باید عدد صفر ارسال شود)
accName : عنوان حساب
accFirstName : نام
accLastName : نام خانوادگی
accFatherName : نام پدر
accAddress : آدرس
accPhone : تلفن
accCellPhone : همراه
accPersonCode : کد پرسنلی
accDescription : شرح
accEconomicNo : شماره اقتصادی
accBirthDate : تاریخ تولد مثال 13610315
accNationalCode : کد ملی
accBirthCity : شهر تولد
accIdNo : شماره شناسنامه

### SaveTafsilAccountL4

- Operation page: [SaveTafsilAccountL4](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=SaveTafsilAccountL4)

ایجاد و یا ویرایش حساب تفصیلی سطح اول
پس از ایجاد و یا ویرایش حساب تفصیلی، در صورت موفق بودن عملیات شناسه حساب#کد حساب تفصیلی برگشت داده خواهد شد
sessionId : شناسه فعالیت گرفته شده در متد Login
accTgCode : کد سرگروه تفصیلی
accCode : کد حساب (در صورت حساب جدید خالی ارسال شود)
accName : عنوان حساب
accFirstName : نام
accLastName : نام خانوادگی
accDSex : جنسیت در صورت حقیقی بودن حساب (آقا / خانم)
accFatherName : نام پدر
accAddress : آدرس
accPhone : تلفن
accCellPhone : همراه
accPersonCode : کد پرسنلی
accDescription : شرح
accEconomicNo : شماره اقتصادی
accBirthDate : تاریخ تولد مثال 13610315
accNationalCode : کد ملی
accBirthCity : شهر تولد
accIdNo : شماره شناسنامه

### StockCountGetGood

- Operation page: [StockCountGetGood](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockCountGetGood)

**برگشت ردیف شمارش موجودی کالا براساس انبار و کد کالا جهت ثبت موجودی شمارش واقعی کالا در انبار**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "stNo":
  "goodFullCode": ""
  "goodBarcode": ""
}
```

**توضیحات کامل پارامترهای JSON:**

- **stNo:** شماره برگه شمارش موجودی انبار

- **goodFullCode:**کد کالا

- **goodBarcode:**بارکد کالا

- توجه
داشته باشید که در صورت ورود کد کالا نیازی به بارکد نیست و جستجو براساس
کد انجام خواهد شد، و یا در صورتی که کد کالا مشخص نباشد می توانید کالای
مورود نظر را براساس بارکد جستجو کنید در این صورت امکان این وجود دارد که
لیست بازگشتی چند ردیف باشد، در صورت ثبت تکراری بارکد کالا

### StockCountGetList

- Operation page: [StockCountGetList](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockCountGetList)

**گرفتن لیست برگه های شمارش موجودی انبار**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "justUnConfirmed": true
}
```

**توضیحات کامل پارامترهای JSON:**

- **justUnConfirmed:** برگشت فقط لیست برگه های تایید نشده و قابل ویرایش، در صورتی که بخواهید همه برگه های شامل لیست باشند مقدار را false ارسال کنید

### StockCountSave

- Operation page: [StockCountSave](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockCountSave)

**ذخیره مقدار شمارش شده در انبار در شمارش موجودی ذکر شده**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParamsArray:** رشته‌ای از نوع آرایه JSON شامل فیلدها. نمونه:

```json
[{
  "stNo":
  "goodFullCode": ""
  "TempCount":
  "TempCount2": null
}]
```

**توضیحات کامل پارامترهای JSON:**

- **goodFullCode:**کد کالا

- **TempCount:**مقدار شمارش اول

- **TempCount2:**مقدار شمارش دوم

- توجه داشته باشید در صورت عدم شمارش دوم مقدار آن را null ارسال نمایید

### StockDeleteByFixNo

- Operation page: [StockDeleteByFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockDeleteByFixNo)

حذف برگه های انبارداری / بازرگانی بر اساس شماره ثابت
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrInStock (رسید انبار متفرقه)
StrSaleProforma (پیش فاکتور فروش)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
StrOutStock (حواله انبار متفرقه)
برای مثال در صورت حذف فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stFixNo : شماره ثابت، حذف سند متناظر با شماره ثابت فوق

### StockDeleteByNo

- Operation page: [StockDeleteByNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockDeleteByNo)

حذف برگه های انبارداری / بازرگانی بر اساس شماره
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrInStock (رسید انبار متفرقه)
StrSaleProforma (پیش فاکتور فروش)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
StrOutStock (حواله انبار متفرقه)
برای مثال در صورت حذف فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stNo : شماره، حذف سند متناظر با شماره فوق

### StockExistByFixNo

- Operation page: [StockExistByFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockExistByFixNo)

چک کردن موجود بودن برگه های انبارداری / بازرگانی بر اساس شماره ثابت
در صورت موجود بودن مقدار برگشتی True و در صورت عدم موجود بودن مقدار برگشتی برابر False خواهد بود
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrInStock (رسید انبار متفرقه)
StrSaleProforma (پیش فاکتور فروش)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
StrOutStock (حواله انبار متفرقه)
برای مثال در صورت حذف فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stFixNo : شماره ثابت

### StockFindGoodBy

- Operation page: [StockFindGoodBy](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockFindGoodBy)

**گرفتن اطلاعات کالا به همراه موجودی و قیمت های فروش**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "goodBarcode": ,
  "goodFullCode": ,
  "goodTechnicalCode": ,
  "goodName": ,
  "generalSearch": ,
  "resultLimit": 50 ,
  "toDate": }
```

**توضیحات کامل پارامترهای JSON:**

- **goodBarcode:** بارکد کالا

- **goodFullCode:** کد کالا

- **goodTechnicalCode:** کدفنی کالا

- **goodName:** نام کالا

- **generalSearch:** جستجو و برگشت نسبت به موجود بودن براساس بارکد، کد کالا، کد فنی و در نهایت نام

- **resultLimit:** محدودیت تعداد آیتم های برگشتی در صورت جستجو بر اساس نام

- **toDate:** تا تاریخ، جهت استخراج موجودی. در صورت خالی شرط تاریخ اعمال نخواهد شد

### StockGetBasicInfo

- Operation page: [StockGetBasicInfo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetBasicInfo)

گرفتن اطلاعات پایه انبارداری و بازرگانی
sessionId : شناسه فعالیت گرفته شده در متد Login
containGoods : شامل فهرست کالا [true or false]
containGoodGroups : شامل گروه بندی کالا ها [true or false]
containStores : شامل انبارها [true or false]
containPriceTitles : شامل عنوان نوع قیمت های فروش [true or false]
containFactorOperatives : شامل عوامل فاکتور [true or false]
containGoodPackingPrice : شامل قیمت های فروش کالا [true or false]

### StockGetByNo

- Operation page: [StockGetByNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetByNo)

گرفتن اطلاعات فاکتور بازرگانی بر اساس شماره
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
برای مثال در صورت درخواست فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stNo : شماره

### StockGetFactorReport

- Operation page: [StockGetFactorReport](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetFactorReport)

**گزارش فاکتورها**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "stEntityType": "StrSaleFactor",
  "stNoFrom": "",
  "stNoTo": "",
  "stFixNoFrom": "",
  "stFixNoTo": "",
  "stDateFrom": "",
  "stDateTo": "",
  "stDesc": "",
  "tgCodes": "",
  "tafsilCodes": "",
  "usersIds": "",
  "stState": "",
  "kol": "",
  "moein": "",
  "tafsil": "",
  "tafsil2": "",
  "tafsil3": "",
  "acclocationIds": "",
  "stGroupBy": "",
  "stOrder": "1"
}
```

**توضیحات کامل پارامترهای ارسالی در فرمت JSON:**

- **stEntityType:** نوع موجودیت، یکی از موارد زیر:

- StrBuyFactor (فاکتور خرید)

- StrBuyReturnFactor (برگشت از خرید)

- StrInStock (رسید انبار متفرقه)

- StrSaleProforma (پیش فاکتور فروش)

- StrSaleOrder (سفارش فروش)

- StrSaleFactor (فاکتور فروش)

- StrSaleReturnFactor (برگشت از فروش)

- StrOutStock (حواله انبار متفرقه)

مثال: برای فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد

- **stNoFrom / stNoTo:** محدوده شماره برگه

- **stFixNoFrom / stFixNoTo:** محدوده شماره ثابت

- **stDateFrom / stDateTo:** محدوده تاریخ (مثال: 13950306)

- **stDesc:** قسمتی از شرح اصلی برگه

- **tgCodes / tafsilCodes / usersIds:** کدهای تفصیلی یا کاربران، جدا شده با کاما (،)

- **stState:** وضعیت برگه:

- : همه

- 0: تایید نشده‌ها

- 1: تایید شده‌ها

- **kol / moein / tafsil / tafsil2 / tafsil3:** کد حساب

- **acclocationIds:** شناسه موقعیت‌های جغرافیایی، جدا شده با کاما

- **stGroupBy:** گروه‌بندی بر اساس:

- 1: روز

- 2: ماه

- 3: فصل

- 4: سال

- 5: نوع موجودیت

- 6: مشتری

- 8: کاربر ایجادکننده

- **stOrder:** ترتیب نمایش:

- 1: شماره

- 2: کد حساب

- 3: تاریخ

- 5: کد نام حساب

### StockGetGoodBy

- Operation page: [StockGetGoodBy](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetGoodBy)

گرفتن اطلاعات کالا بر اساس کد
sessionId : شناسه فعالیت گرفته شده در متد Login
goodFullCode : کد کامل کالا
goodTechnicalCode : کد فنی کالا
در صورتی که بخواهید کالا را بر اساس کد فنی جستجو کنید کد کامل کالا را خالی ارسال کنید یا برعکس

### StockGetGoodCount

- Operation page: [StockGetGoodCount](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetGoodCount)

گرفتن موجودی کالا
sessionId : شناسه فعالیت گرفته شده در متد Login
goodStoreIds
: شناسه انبارها [در صورت چندین انبار شناسه انبارها با کارکتر (,) از هم
جدا شوند برای مثال 1,2 و در صورت درخواست موجودی تمامی انبارها خالی ارسال
شود]
goodIds : شناسه کالاها [در صورت چندین کالا شناسه کالاها با
کارکتر (,) از هم جدا شوند برای مثال 1,2 و در صورت درخواست موجودی تمامی
کالاها خالی ارسال شود]
toDate : تا تاریخ [مثال 13990720 در صورتی که بخواهید محدودیت تاریخ اعمال نشود عدد صفر را ارسال کنید]
toTime : تا ساعت [مثال 132005 در صورتی که بخواهید محدودیت تاریخ اعمال نشود عدد صفر را ارسال کنید]

### StockGetGoodCountWithQc

- Operation page: [StockGetGoodCountWithQc](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetGoodCountWithQc)

گرفتن موجودی کالا
sessionId : شناسه فعالیت گرفته شده در متد Login
goodStoreIds
: شناسه انبارها [در صورت چندین انبار شناسه انبارها با کارکتر (,) از هم
جدا شوند برای مثال 1,2 و در صورت درخواست موجودی تمامی انبارها خالی ارسال
شود]
goodIds : شناسه کالاها [در صورت چندین کالا شناسه کالاها با
کارکتر (,) از هم جدا شوند برای مثال 1,2 و در صورت درخواست موجودی تمامی
کالاها خالی ارسال شود]
toDate : تا تاریخ [مثال 13990720 در صورتی که بخواهید محدودیت تاریخ اعمال نشود عدد صفر را ارسال کنید]
toTime : تا ساعت [مثال 132005 در صورتی که بخواهید محدودیت تاریخ اعمال نشود عدد صفر را ارسال کنید]

### StockGetGoodReport

- Operation page: [StockGetGoodReport](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetGoodReport)

**گزارش مقداری و ریالی کالا (عوامل فاکتور)**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **stEntityType:** نوع موجودیت، یکی از موارد زیر:

- StrBuyFactor (فاکتور خرید)

- StrBuyReturnFactor (برگشت از خرید)

- StrInStock (رسید انبار متفرقه)

- StrSaleProforma (پیش فاکتور فروش)

- StrSaleOrder (سفارش فروش)

- StrSaleFactor (فاکتور فروش)

- StrSaleReturnFactor (برگشت از فروش)

- StrOutStock (حواله انبار متفرقه)

مثال: برای فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد

- **stdGsIds:** کد انبارها، جدا شده با کاما (مثال: 1,2)

- **stDateFrom:** از تاریخ (مثال: 13950306)

- **stDateTo:** تا تاریخ (مثال: 13950306)

- **goodFullCodeFrom:** از کد کالا

- **goodFullCodeTo:** تا کد کالا

- **goodName:** نام کالا

- **kolFrom / moeinFrom / tafsilFrom:** از حساب‌ها

- **kolTo / moeinTo / tafsilTo:** تا حساب‌ها

- **groupBy:** گروه‌بندی بر اساس:

- 1: روز

- 2: ماه

- 3: فصل

- 4: سال

- 12: حساب

- 21: حساب معین

- 13: کاربر ایجادکننده

- 14: کالا

- 15: گروه کالا

- 16: انبار

- **operativeGroupType:** گروه‌بندی عوامل:

- 1: بر اساس نوع عوامل

- 0: بر اساس عوامل

### StockGetGoods

- Operation page: [StockGetGoods](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetGoods)

گرفتن اطلاعات اصلی کالا به همراه مانده موجودی و قیمت های فروش
sessionId : شناسه فعالیت گرفته شده در متد Login
hasRemCount : فقط کالاهای دارای مانده [true or false]
goodFullCodeStartWith : کالاهای شروع شده با کد فوق
withGoodPackingPrice : به همراه قیمت های فروش [true or false]
goodCreateDateFrom : کالاهای ایجاد شده از تاریخ - مثال 14030301
goodCreateDateTo : کالاهای ایجاد شده تا تاریخ - مثال 14030310
goodUpdateDateFrom : کالاهای ویرایش شده از تاریخ - مثال 14030301
goodUpdateDateTo : کالاهای ویرایش شده تا تاریخ - مثال 14030310

### StockGetNewFixNo

- Operation page: [StockGetNewFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetNewFixNo)

چک کردن موجود بودن برگه های انبارداری / بازرگانی بر اساس شماره ثابت
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrInStock (رسید انبار متفرقه)
StrSaleProforma (پیش فاکتور فروش)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
StrOutStock (حواله انبار متفرقه)
برای مثال در صورت حذف فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد

### StockGetOrderControlReport

- Operation page: [StockGetOrderControlReport](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGetOrderControlReport)

**گزارش کنترل سفارش‌ فروش**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. ساختار:

```json
{
  "stDateFrom": "",
  "stDateTo": "",
  "stNoFrom": "",
  "stNoTo": "",
  "controlType": "0",
  "kol": "",
  "moein": "",
  "tafsil": "",
  "tafsil2": "",
  "tafsil3": "",
  "usersIds": "",
  "showByRemind": "",
  "includeClosed": "",
  "stState": ""
}
```

**توضیحات کامل پارامترهای JSON:**

- **stDateFrom / stDateTo:** محدوده تاریخ سفارش‌ها (فرمت: yyyy/MM/dd)

- **stNoFrom / stNoTo:** محدوده شماره سفارش

- **controlType:** نوع کنترل سفارش:

- 0: مقایسه سفارش با **فاکتور فروش**

- 1: مقایسه سفارش با **حواله فروش**

- **kol / moein / tafsil / tafsil2 / tafsil3:** کد حساب مالی مرتبط با سفارش

- **usersIds:** شناسه کاربران ثبت‌کننده سفارش، جدا شده با کاما (مثال: 1,2)

- **showByRemind:** فقط سفارش‌هایی که **دارای مانده** هستند نمایش داده شوند (true/false)

- **includeClosed:** آیا سفارش‌های **بسته‌شده** نیز در گزارش لحاظ شوند؟ (true/false)

- **stState:** وضعیت سفارش:

- : همه

- 0: تایید نشده

- 1: تایید شده

- 2: بسته شده

- 3: بسته شده (تایید شده)

- 4: بسته شده (تایید نشده)

### StockGoodSave

- Operation page: [StockGoodSave](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGoodSave)

ذخیره اطلاعات کالا
sessionId : شناسه فعالیت گرفته شده در متد Login
actionType : نوع فعالیت
Insert (ایجاد)
Update (ویرایش)
goodType : نوع کالا
0 : سایر کالاها
1 : مواد
2 : ملزومات و قطعات
3 : کالای در جریان ساخت
4 : محصول
5 : ضایعات
6 : خدمات
goodComment : توضیحات کالا
goodFullcode : کد کامل کالا
goodGroupFullcode : کد کامل گروه کالا
goodTechnicalCode : کد فنی کالا
goodName : نام کالا
goodTaxCode : کد مالیاتی کالا
goodTaxName : نام مالیاتی کالا
goodStockControl : کنترل شدن موجودی کالا
1 : کنترل شدن موجودی
0 : کنترل نشدن موجودی
goodIsActive : فعال بودن کالا
1 : فعال
0 : غیرفعال
goodTaxPercent : درصد مالیات بر ارزش افزوده کالا
goodEnableSerial : سریال پذیر بودن کالا
0 : سریال پذیر نمی باشد
1 : سریال پذیر می باشد
goodEnableQc : کنترل کمی پذیر بودن کالا
0 : کنترل کمی پذیر نمی باشد
1 : کنترل کمی پذیر می باشد
goodIndependSubUnits : فعال بودن واحد فرعی مستقل
0 : واحد فرعی مستقل فعال نمی باشد
1 : واحد فرعی مستقل فعال می باشد
goodCustomerPrice : قیمت مصرف کننده
goodBarcodes : بارکد های کالا : مثال بارکد اول، بارکد دوم
برای جداسازی بارکد ها از کارکتر (،) استفاده گردد
goodUnitName : نام واحد اصلی کالا
goodUnitWeight : وزن خالص واحد اصلی کالا (گرم)
goodUnitPackingWeight : وزن به همراه بسته بندی واحد اصلی کالا (گرم)
goodSalePrice : قیمت های فروش کالا کالا
0 : نوع قیمت فروش:مبلغ قیمت فروش،نوع قیمت فروش:مبلغ قیمت فروش
0 : مثال نقدی:125000،نسیه:135000
0 : برای قیمت های بعدی نیز همین ساختار رعایت شود
goodOtherUnitAndPrice : واحدهای ثانویه کالا
در
صورتی که بخواهید برای کالای فوق بیش از یک واحد، قیمت و وزن های مختلف
برای هر یک از واحدهای ذکر شده ثبت نمایید از این گزینه با فرمت ذکر شده
استفاده نمایید
جهت جداسازی واحدها : ##
واحد
کالا|معادل در واحد اصلی|وزن خالص گرم|وزن ناخالص گرم|نوع قیمت فروش:مبلغ
قیمت فروش،نوع قیمت فروش:مبلغ قیمت فروش : فرمت
توجه
داشته باشید که از فرمت بالا واحد کالا و معادل در واحد اصلی اجباری می
باشد، در صورتی که کالا قیمت فروش داشته اما اطلاعات وزن را نداشته باشیم
باید فرمت ذکر شده رعایت شده و جای وزن را خالی یا صفر بگذارید
مثال : بسته|3|0|0|نقدی:125000،نسیه:135000##کارتن|12|0|0|نقدی:150000،نسیه:1620000
goodStoreIds
: کد انبارهای مرتبط با کالای فوق در صورتی
که بخواهید کالای فوق در انبارهای خاصی گردش داشته باشد می توانید کد
انبارها را به صورت متن و جداساز کاما ارسال کنید
در صورتی که بخواهید کالا در تمامی انبارها گردش داشته باشد گزینه فوق را خالی ارسال نمایید
برای
مثال در صورتی که بخواهیم کالای فوق فقط در انبار 2 و 10 گردش داشته باشد
مقدار فوق را ارسال می کنیم : 2,10

### StockGoodSaveV2

- Operation page: [StockGoodSaveV2](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockGoodSaveV2)

**ذخیره / ویرایش اطلاعات کالا**

- **sessionId:** شناسه فعالیت گرفته شده از متد Login

- **jsonParams:** رشته JSON شامل اطلاعات کامل کالا

**نمونه JSON:**

```json
{
  "actionType": "Insert",
  "goodType": "1",
  "goodFullcode": "1001",
  "goodGroupFullcode": "10",
  "goodTechnicalCode": "",
  "goodName": "کالای تست",
  "goodComment": "توضیحات",
  "goodTaxCode": "123",
  "goodTaxName": "نام مالیاتی کالا",
  "goodStockControl": "1",
  "goodIsActive": "1",
  "goodTaxPercent": "10",
  "goodEnableSerial": "0",
  "goodEnableQc": "0",
  "goodIndependSubUnits": "0",
  "goodCustomerPrice": "0",
  "goodBarcodes": "12345،67890",
  "goodUnitName": "عدد",
  "goodUnitWeight": "0",
  "goodUnitPackingWeight": "0",
  "goodSalePrice": "نقدی:125000،نسیه:135000",
  "goodOtherUnitAndPrice": "بسته|3|0|0|نقدی:125000،نسیه:135000##کارتن|12|0|0|نقدی:150000،نسیه:162000",
  "goodStoreIds": "2,10",
  "goodPackingNames": ""
}
```

**توضیحات پارامترها:**

- **actionType:** نوع عملیات
Insert : ایجاد
Update : ویرایش

- **goodType:** نوع کالا
0 : سایر کالاها
1 : مواد
2 : ملزومات و قطعات
3 : کالای در جریان ساخت
4 : محصول
5 : ضایعات
6 : خدمات

- **goodFullcode:** کد کامل کالا

- **goodGroupFullcode:** کد کامل گروه کالا

- **goodTechnicalCode:** کد فنی کالا (در صورت عدم استفاده مقدار 0 ارسال شود)

- **goodName:** نام کالا

- **goodComment:** توضیحات کالا

- **goodTaxCode:** کد مالیاتی

- **goodTaxName:** نام مالیاتی

- **goodTaxPercent:** درصد مالیات

- **goodStockControl:**
1 : کنترل موجودی
0 : عدم کنترل موجودی

- **goodIsActive:**
1 : فعال
0 : غیرفعال

- **goodEnableSerial:**
1 : سریال پذیر
0 : غیر سریال پذیر

- **goodEnableQc:**
1 : دارای کنترل کمی
0 : بدون کنترل کمی

- **goodIndependSubUnits:**
1 : دارای واحد فرعی مستقل
0 : ندارد

- **goodCustomerPrice:** قیمت مصرف‌کننده

- **goodBarcodes:** بارکدها (با جداکننده '،')

- **goodUnitName:** نام واحد اصلی

- **goodUnitWeight:** وزن خالص (گرم)

- **goodUnitPackingWeight:** وزن با بسته‌بندی (گرم)

- **goodSalePrice:**
قیمت های فروش کالا
فرمت: نوع قیمت فروش:مبلغ قیمت فروش،نوع قیمت فروش:مبلغ قیمت فروش
مثال: نقدی:125000،نسیه:135000

- **goodOtherUnitAndPrice:**
واحدهای ثانویه کالا
در
صورتی که بخواهید برای کالای فوق بیش از یک واحد، قیمت و وزن های مختلف
برای هر یک از واحدهای ذکر شده ثبت نمایید از این گزینه با فرمت ذکر شده
استفاده نمایید
فرمت:
واحد کالا|معادل در واحد اصلی|وزن خالص گرم|وزن ناخالص گرم|نوع قیمت فروش:مبلغ قیمت فروش،نوع قیمت فروش:مبلغ قیمت فروش
جداکننده واحدها: ##
مثال:
بسته|3|0|0|نقدی:125000،نسیه:135000##کارتن|12|0|0|نقدی:150000،نسیه:162000

- **goodStoreIds:**
کد انبارهای مرتبط با کالای فوق
در
صورتی که بخواهید کالای فوق در انبارهای خاصی گردش داشته باشد می توانید
کد انبارها را به صورت متن و جداساز کاما ارسال کنید
مثال: 2,10
در صورت خالی بودن → کالا در همه انبارها فعال خواهد بود

- **goodPackingNames:**
لیست طبقه بندی کالا با جداکننده ','
مثال: برند آلفا,تامین کننده الماس
در صورت خالی بودن → عدم اختصاص دادن به طبقه بندی خاص

### StockPrintAsPdf

- Operation page: [StockPrintAsPdf](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockPrintAsPdf)

چاپ برگه های بازرگانی بر اساس شماره تبدیل فایل PDF گزارش به Base64String
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
printType : نوع چاپ
0 (چاپ فاکتور)
1 (چاپ ریز عوامل و چک ها)
2 (چاپ فرمت دارائی)
برای مثال در صورت حذف فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stNo : شماره، چاپ سند متناظر با شماره فوق

### StockSave

- Operation page: [StockSave](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockSave)

ذخیره برگه های انبارداری و بازرگانی
sessionId : شناسه فعالیت گرفته شده در متد Login
actionType : نوع فعالیت
Insert (ایجاد)
Update (ویرایش)
stEntityType : نوع موجودیت
StrInBuy (رسید خرید نهایی)
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrInStock (رسید انبار متفرقه)
StrSaleProforma (پیش فاکتور فروش)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
StrOutStock (حواله انبار متفرقه)
برای مثال در صورت ثبت فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stNo : شماره برگه (در صورتی که بخواهید شماره توسط سیستم اختصاص داده شود خالی یا صفر ارسال شود)
stFixNo : شماره ثابت برگه
stDate : تاریخ، مثال 13950306
stTime : ساعت، مثال 142501
stDeliveryDate : تاریخ، مثال 13950306
stDesc : شرح برگه
stFlag : وضعیت برگه -> 0 (تایید نشده) و 1 (تایید شده(
stKol : کد حساب کل
stMoein : کد حساب معین
stTafsil : کد حساب تفصیلی
stTafsil2 : کد حساب تفصیلی 2
stTafsil3 : کد حساب تفصیلی 3
stSegment : کد مرکز
stDetails
: [{"StdGsId":"کد انبار","GoodFullCode":"کد
کالا","StdCount":"تعداد","StdPrice":"قیمت
واحد","StdDesc":"شرح","Operatives": "عنوان عامل:مقدار عامل,عنوان
عامل:مقدارعامل, ...","QcNo":"کد کنترل کمی"}]
فرمت ردیف های برگه به شکل بالا بوده و برای سایر ردیف ها تکرار خواهد شد
نحوه
ثبت عوامل ردیفی به صورت عنوان و مقدار عامل خواهد بود، در صورت استفاده
بیش از یک عامل توسط کارکتر کاما از هم جدا گردد
فرمت قیمت به صورت عدد می باشد و از جداساز مبلغ استفاده نشود، مثال 1250000 ریال
"operatives":[{"Title":"عنوان عامل","Value":"مقدار","Description":"توضیحات"}]
فرمت عوامل پای فاکتور به صورت بالا بوده و برای سایر عوامل پای فاکتور تکرار خواهد شد

### StockSaveV2

- Operation page: [StockSaveV2](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=StockSaveV2)

ذخیره برگه های انبارداری و بازرگانی
sessionId : شناسه فعالیت گرفته شده در متد Login
actionType : نوع فعالیت
Insert (ایجاد)
Update (ویرایش)
stEntityType : نوع موجودیت
StrInBuy (رسید خرید نهایی)
StrBuyFactor (فاکتور خرید)
StrBuyReturnFactor (برگشت از خرید)
StrInStock (رسید انبار متفرقه)
StrSaleProforma (پیش فاکتور فروش)
StrSaleOrder (سفارش فروش)
StrSaleFactor (فاکتور فروش)
StrSaleReturnFactor (برگشت از فروش)
StrOutStock (حواله انبار متفرقه)
برای مثال در صورت ثبت فاکتور فروش مقدار ارسالی باید برابر StrSaleFactor باشد
stNo : شماره برگه (در صورتی که بخواهید شماره توسط سیستم اختصاص داده شود خالی یا صفر ارسال شود)
stFixNo : شماره ثابت برگه
stDate : تاریخ، مثال 13950306
stTime : ساعت، مثال 142501
stDeliveryDate : تاریخ، مثال 13950306
stDesc : شرح برگه
stFlag : وضعیت برگه -> 0 (تایید نشده) و 1 (تایید شده(
stKol : کد حساب کل
stMoein : کد حساب معین
stTafsil : کد حساب تفصیلی
stTafsil2 : کد حساب تفصیلی 2
stTafsil3 : کد حساب تفصیلی 3
stSegment : کد مرکز
stTypeTitle : نوع (عنوان نوع های تعریف شده برای موجودیت خواسته شده)
stSettleTypeTitle : نوع تسویه (عنوان نوع تسویه خواسته شده)
stDetails
: [{"StdGsId":"کد انبار","GoodFullCode":"کد
کالا","StdCount":"تعداد","StdPrice":"قیمت
واحد","StdDesc":"شرح","Operatives": "عنوان عامل:مقدار عامل,عنوان
عامل:مقدارعامل, ...","QcNo":"کد کنترل کمی"}]
فرمت ردیف های برگه به شکل بالا بوده و برای سایر ردیف ها تکرار خواهد شد
نحوه
ثبت عوامل ردیفی به صورت عنوان و مقدار عامل خواهد بود، در صورت استفاده
بیش از یک عامل توسط کارکتر کاما از هم جدا گردد
فرمت قیمت به صورت عدد می باشد و از جداساز مبلغ استفاده نشود، مثال 1250000 ریال
"operatives":[{"Title":"عنوان عامل","Value":"مقدار","Description":"توضیحات"}]
فرمت عوامل پای فاکتور به صورت بالا بوده و برای سایر عوامل پای فاکتور تکرار خواهد شد

### SysPosDoPayment

- Operation page: [SysPosDoPayment](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=SysPosDoPayment)

**ارسال مبلغ به پوز جهت پرداخت توسط مشتری**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "PosId": ,
  "PaymentAmount": ,
  "InvoiceNumber":
}
```

**توضیحات کامل پارامترهای JSON:**

- **PosId:**شناسه پوز موجود در لسیت پوزها گرفته شده در متد SysPosGetList

- **PaymentAmount:**مبلغ بدون جدا کننده و اعشار مانند 5000000

- **InvoiceNumber:**شماره فاکتور

### SysPosGetList

- Operation page: [SysPosGetList](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=SysPosGetList)

گرفتن لیست پوزها جهت ارسال مبلغ و کشیدن کارت توسط مشتری
sessionId : شناسه فعالیت گرفته شده در متد Login

### TreasuryDeleteByFixNo

- Operation page: [TreasuryDeleteByFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=TreasuryDeleteByFixNo)

حذف برگه های خزانه داری بر اساس شماره ثابت
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
Receive (سند دریافت)
Payment (سند پرداخت)
برای مثال در صورت حذف سند دریافت مقدار ارسالی باید برابر Receive باشد
mvFixNo : شماره ثابت، حذف سند متناظر با شماره ثابت فوق

### TreasuryDeleteByNo

- Operation page: [TreasuryDeleteByNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=TreasuryDeleteByNo)

حذف برگه های خزانه داری بر اساس شماره
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
Receive (سند دریافت)
Payment (سند پرداخت)
برای مثال در صورت حذف سند دریافت مقدار ارسالی باید برابر Receive باشد
mvFixNo : شماره ، حذف سند متناظر با شماره فوق

### TreasuryExistByFixNo

- Operation page: [TreasuryExistByFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=TreasuryExistByFixNo)

چک کردن موجود بودن برگه های خزانه داری بر اساس شماره ثابت
در صورت موجود بودن مقدار برگشتی True و در صورت عدم موجود بودن مقدار برگشتی برابر False خواهد بود
sessionId : شناسه فعالیت گرفته شده در متد Login
stEntityType : نوع موجودیت
Receive (سند دریافت)
Payment (سند پرداخت)
mvFixNo : شماره ثابت

### TreasuryGetChecks

- Operation page: [TreasuryGetChecks](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=TreasuryGetChecks)

گرفتن فهرست چکها دریافتی یا پرداختی
sessionId : شناسه فعالیت گرفته شده در متد Login
entityType : نوع چک
Receive (اسناد دریافتی)
Payment (اسناد پرداختی)
checkNo : شماره چک
checkPriceFrom : مبلغ چک از، مثال 50000000
checkPriceTo : مبلغ چک تا، مثال 60000000
checkDateFrom : تاریخ دریافت یا پرداخت چک از، مثال 13950306
checkDateTo : تاریخ دریافت یا پرداخت چک تا، مثال 13950407
checkUsanceDateFrom : تاریخ سررسید چک از، مثال 13950306
checkUsanceDateTo : تاریخ سررسید چک تا، مثال 13950407
checkCashDateFrom : تاریخ وصول یا نقد شدن چک از، مثال 13950306
checkCashDateTo : تاریخ وصول یا نقد شدن چک تا، مثال 13950407

### TreasuryGetNewFixNo

- Operation page: [TreasuryGetNewFixNo](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=TreasuryGetNewFixNo)

چک کردن موجود بودن برگه های انبارداری / بازرگانی بر اساس شماره ثابت
sessionId : شناسه فعالیت گرفته شده در متد Login
rpEntityType : نوع موجودیت
Receive (سند دریافت)
Payment (سند پرداخت)

### TreasurySave

- Operation page: [TreasurySave](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=TreasurySave)

ذخیره سند دریافت / پرداخت
sessionId : شناسه فعالیت گرفته شده در متد Login
actionType : نوع فعالیت
Insert (ایجاد)
Update (ویرایش)
rpEntityType : نوع موجودیت خواسته شده جهت ذخیره
Receive (سند دریافت)
Payment (سند پرداخت)
برای مثال در صورت ثبت سند دریافت مقدار ارسالی باید برابر Receive باشد
rpNo : شماره - در صورتی که خالی یا صفر ارسال شود سیستم به صورت خودکار شماره جدید را انتصاب خواهد داد
rpFixNo : شماره ثابت
rpDate : تاریخ، مثال 13950306
rpTime : ساعت، مثال 132504
rpDesc : شرح اصلی برگه
rpKol : کد حساب کل دریافت کننده / پرداخت کننده
rpMoein : کد حساب معین دریافت کننده / پرداخت کننده
rpTafsil : کد حساب تفصیلی دریافت کننده / پرداخت کننده
rpTafsil2 : کد حساب تفصیلی 2 دریافت کننده / پرداخت کننده
rpTafsil3 : کد حساب تفصیلی 3 دریافت کننده / پرداخت کننده
rpSegment : کد مرکز هزینه یا پروژه
rpFlag : وضعیت -> 0 (تایید نشده) و 1 (تایید شده(
rpDetails
: [{"RpdKol":"کد حساب کل دریافت کننده / پرداخت کننده","RpdMoein":"کد
حساب معین دریافت کننده / پرداخت کننده","RpdTafsil":"کد حساب تفصیلی
دریافت کننده / پرداخت کننده","RpdTafsil2":"کد حساب تفصیلی 2 دریافت کننده
/ پرداخت کننده","RpdTafsil3":"کد حساب تفصیلی 3 دریافت کننده / پرداخت
کننده","RpdPrice":"مبلغ","RpdBedDesc":"شرح بدهکاری","RpdBesDesc":"شرح
بستانکاری","RpdTrackingNo":"شماره پیگیری"}]
فرمت ردیف های دریافت / پرداخت به شکل بالا بوده و برای سایر ردیف ها تکرار خواهد شد
فرمت مبلغ ها به صورت عدد می باشد و از جداساز مبلغ استفاده نشود، مثال 1250000 ریال

### VisitorLocationSave

- Operation page: [VisitorLocationSave](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=VisitorLocationSave)

**ذخیره موقعیت ویزیتور**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل سایر پارامترها. نمونه:

```json
{
  "userId": ,
  "customerAccId": ,
  "date": ,
  "time": ,
  "position": "",
  "desc": "",
  "attrib": ""
}
```

**توضیحات کامل پارامترهای JSON:**

- **userId:** شناسه کاربر، در صورت خالی یا صفر بودن شناسه کاربر جاری ست خواهد شد

- **customerAccId:** شناسه حساب تفصیلی مشتری، در صورت عدم وجود خالی یا صفر ارسال گردد

- **date:** تاریخ، در صورت خالی یا صفر توسط سیستم ست خواهد شد

- **time:** ساعت، در صورت خالی یا صفر تاریخ توسط سیستم ست خواهد شد

- **position:** موقعیت GPS ویزیتور به فرمت Latitude, Longitude

- **desc:** توضیحات اختیاری

- **attrib:** ویژگی های سفارشی

### VisitorLocationSearch

- Operation page: [VisitorLocationSearch](https://www.mehrsofts.com/webservice/mehraccws.asmx?op=VisitorLocationSearch)

**جستجوی موقعیت‌های ویزیتور**

- **sessionId:** شناسه فعالیت گرفته شده در متد Login

- **jsonParams:** رشته‌ای از نوع JSON شامل پارامترها. نمونه:

```json
{
  "userId": ,
  "fromDate": ,
  "toDate": ,
  "desc": ""
}
```

**توضیحات کامل پارامترهای JSON:**

- **userId:** شناسه کاربر، در صورت نیاز به تمامی موقعیت های کاربران خالی یا صفر ارسال گردد

- **fromDate / toDate:** بازه تاریخ، در صورت خالی بودن شرط تاریخ اعمال نخواهد شد

- **desc:** جستجو براساس بخشی از توضیحات
