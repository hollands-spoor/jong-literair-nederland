import polib

po = polib.pofile('languages/x-literair-nederland-blocks-nl_NL.po')
blanks = [e.msgid for e in po if (not e.obsolete and not e.msgstr)]
print('Untranslated:', len(blanks))
for i, msgid in enumerate(blanks[:120], 1):
    print('{:03d}: {}'.format(i, msgid))
