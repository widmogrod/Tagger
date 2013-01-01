# Tagger
## General
Playground for the different ways of extracting keywords from website.
Still in development phase.

## Statistical aproche.
Simple implementation exists.

## Neural net concept.
### General
Extract words from document and using "features" of words (eg DOM element in which is embedded, position in document,...)
Prepara learning data...

### Mind draft.

- fetch document (web site)
- tokenize words
- extract word features eg:
 - tag (h1,...a)
 - position in document (top, middle, bottom, first screen)
 - occurences
 - word length
 - popularity (in context of different documents?)

### Data draft.

``` 
word		 | input (word features) | output
--------------------------------------
bodypainting | [title, top,    5]    | important keyword
some		 | [p,     middle, 15]   | not important keyword
```