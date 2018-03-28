# PHP Po Editor

##Install
* composer require ics1/poeditor:"dev-master"

## Usage
### Parsing and Save Files

```php
$po = new ics1\PoEditor\PoEditor('en_US.po');
$po->parse();
$po->getItem('Book')->setMsgStr("Книга");
$po->getItem('New')->setMsgStr("Новый");
$po->save();
```