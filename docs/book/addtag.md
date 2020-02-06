# add new tag

onePlace Tag comes with "Category" and "State" Tag by default.
You can add any tag you want to enhance your experience with tag module.

All you have to do, is to add it to the `core_tag` table. there you can
also check for other tags maybe be added by other modules already.

You have to define a `tag_key` (english, no special characters, 1 word)
and a `tag_label` (should be english can be translated in mo files)

In this example we add a tag called `Deliverytime` - which is then available
for all entities in oneplace.

```sql
INSERT INTO `core_tag` (`Tag_ID`, `tag_key`, `tag_label`, `created_by`, `created_date`, `modified_by`, `modified_date`) VALUES
(NULL, 'deliverytime', 'Deliverytime', 1, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00');
```

You can now use your new tag e.G for select fields in your forms 
> /tag/api/list/yourform-single/deliverytime