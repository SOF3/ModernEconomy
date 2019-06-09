---
title: YAML Help
---

Note: This is an unofficial documentation of YAML, dedicated for ModernEconomy configuration (and useful for most other PocketMine-related YAML files).

### Comment
Lines starting with `#` are there to explain the config. You are free to delete or modify them without changing anything.
(Changing comments won't even cause warnings like "synchronized configuration is different")

### Boolean
Boolean is `true` or `false`. Examples:
```yaml
enabled: true
```
```yaml
enabled: false
```

### Integer
An integer is a zero, positive or negative number, with less than 20 digits. Examples:
```yaml
amount: 5678
```
```yaml
amount: -1234
```

### Number
> Known as `float` in the official YAML specification.

A number is any real number. All integers are valid numbers. Examples:
```yaml
ratio: -1234.5678
```
```yaml
ratio: 5678
```
```yaml
ratio: 5678.0
```
(The two are identical)

> Although `NaN` and `Inf` are allowed in the YAML specification, ModernEconomy usually does not accept these values unless specified.

### Text
> Known as `string` in the official YAML specification.

A text should always be enclosed by `""`.
If the text is intended to be multi-line, put `\n` at the place supposed to be the newline.
If you want to type `"`, type `\"` instead. Examples:
```yaml
name: "SOFe"
```
```yaml
name: "12345"
```
```yaml
name: "yes"
```
```yaml
name: "[]{}\n()*!@#"
```
```yaml
name: "the \"escaped quote\" is like this"
```

> Although quotes are not required by the YAML specification, it is advised that users always add quotes to prevent strange behaviour.

### List/Set
> Known as `sequence` in the official YAML specification.

A list is multiple items of the same type, where the order matters.
A set is multiple items of the same type, where duplicates are not allowed and order does not matter.
(Sets will be automatically sorted no matter how you order the items)
Elements of a sequence a listed in by putting <code>&nbsp;&nbsp;-&nbsp;</code> (two spaces before, one space after) in front of the value.
Examples:
```yaml
worlds:
  - "main"
  - "nether"
```

Lists/Sets may contain any values, including other lists/sets or mappings.
The syntax (especially indentation) for a list of mappings might seem a bit weird:
```yaml
plugins:
  - name: "ModernEconomy"
    authors:
      - "SOFe"
      - "JackMD"
  - name: "LessIsMore"
    authors:
      - "SOFe"
```

> The YAML specification allows any constant number of spaces for indentation, but for simplicity we would assume 2.

### Mapping
A mapping contains different "keys" and "values".
Keys are always text, and values are of different types as required by the plugin.

> Although the official YAML specification allows any mixed types of keys, this is not possible in PHP.
