# Reference md

Generates markdown reference files for interfaces, classes, exceptions and traits in a PHP codebase. Provides indexable human-readable documents enabling your users to easily understand how-to use your software.

## Usage

```php
php parse.php \
    -s source_dir \
    -p source_dir_to_scan \
    -o output_dir \
    -b base_url \
```

Example:

```php
php parse.php \
    -s ~/git/chevere/chevere/ \
    -p src/Chevere/ \
    -o ~/git/chevere/docs/reference/ \
    -b https://github.com/chevere/chevere/blob/main/src/Chevere/
```
