# Fedora Connector

Connect an Omeka S instance to a Fedora 4 repository. Items can be imported from Fedora containers, with optional import of their files.

Information about the original item and a link back to it will be included on the imported item's page.

## Installation

See general end user documentation for [Installing a module](https://github.com/omeka/omeka-s-enduser/blob/master/modules/modules.md#installing-modules)

## Configuration

Optionally import the Fedora Vocabulary and Linked Data Platform Vocabulary. If you do so, data in these vocabularies will also be imported into Omeka S.

## Usage

### Importing

From the main navigation on the left of the admin screen, click Fedora Connector. 

1. Enter the URI of a Fedora 4 container you want to import.
*This module only works with Fedora 4 or higher*

1. Choose whether to import files.

1. Leave a comment about the import. This will appear on the `Past Imports` page, so you have a record of why you did the import (or any other information). Optional.

1. Choose an Item Set to assign imported items to. Item Sets must be created first. Optional.

1. Hit Submit.

### Undoing imports

Click `Fedora Connector`, then the new link for `Past Imports`. Check the boxes for the imports you want to undo, then submit.

