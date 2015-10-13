# Fedora Connector

Connect an Omeka S instance to a Fedora 4 repository. Items can be imported from Fedora containers, with optional import of their files.

Information about the original item and a link back to it will be included on the imported item's page.

## Installation

Download the released zip file, or pull the master branch into Omeka S's `modules` directory.

From the Modules page, install the module.

## Configuration

Optionally import the Fedora Vocabulary and Linked Data Platform Vocabulary. If you do so, data in these vocabularies will also be imported into Omeka S.

## Usage

### Importing

From the main navigation on the left of the admin screen, click `Fedora Connector`. 

Enter the URI of a Fedora 4 container you want to import.

Choose whether to import files.

Leave a comment about the import. This will appear on the `Past Imports` page, so you have a record of why you did the import (or any other information). Optional.

Choose an Item Set for the imported items. Item Sets must be created first. Optional.

Hit Submit.

### Undoing imports

Click `Fedora Connector`, then the new link for `Past Imports`. Check the boxes for the imports you want to undo, then submit.

