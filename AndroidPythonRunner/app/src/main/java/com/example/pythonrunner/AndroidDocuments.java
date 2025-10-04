package com.example.pythonrunner;

import android.content.Context;
import android.database.Cursor;
import android.net.Uri;
import android.provider.DocumentsContract;

import java.util.ArrayList;
import java.util.List;

public class AndroidDocuments {

    // Lists children of the ROOT of the picked tree.
    public static List<Uri> listChildren(Context context, Uri treeUri) {
        List<Uri> results = new ArrayList<>();
        Uri childrenUri = DocumentsContract.buildChildDocumentsUriUsingTree(treeUri,
                DocumentsContract.getTreeDocumentId(treeUri));
        Cursor c = context.getContentResolver().query(childrenUri,
                new String[]{DocumentsContract.Document.COLUMN_DOCUMENT_ID,
                        DocumentsContract.Document.COLUMN_MIME_TYPE,
                        DocumentsContract.Document.COLUMN_DISPLAY_NAME},
                null, null, null);
        if (c != null) {
            try {
                while (c.moveToNext()) {
                    String documentId = c.getString(0);
                    Uri docUri = DocumentsContract.buildDocumentUriUsingTree(treeUri, documentId);
                    results.add(docUri);
                }
            } finally {
                c.close();
            }
        }
        return results;
    }

    // Lists children of an arbitrary directory within the same tree, given its documentId.
    public static List<Uri> listChildren(Context context, Uri treeUri, String parentDocumentId) {
        List<Uri> results = new ArrayList<>();
        Uri childrenUri = DocumentsContract.buildChildDocumentsUriUsingTree(treeUri, parentDocumentId);
        Cursor c = context.getContentResolver().query(childrenUri,
                new String[]{DocumentsContract.Document.COLUMN_DOCUMENT_ID,
                        DocumentsContract.Document.COLUMN_MIME_TYPE,
                        DocumentsContract.Document.COLUMN_DISPLAY_NAME},
                null, null, null);
        if (c != null) {
            try {
                while (c.moveToNext()) {
                    String documentId = c.getString(0);
                    Uri docUri = DocumentsContract.buildDocumentUriUsingTree(treeUri, documentId);
                    results.add(docUri);
                }
            } finally {
                c.close();
            }
        }
        return results;
    }

    public static boolean isDirectory(Context context, Uri docUri) {
        Cursor c = context.getContentResolver().query(docUri,
                new String[]{DocumentsContract.Document.COLUMN_MIME_TYPE},
                null, null, null);
        if (c != null) {
            try {
                if (c.moveToFirst()) {
                    String mime = c.getString(0);
                    return DocumentsContract.Document.MIME_TYPE_DIR.equals(mime);
                }
            } finally {
                c.close();
            }
        }
        return false;
    }

    public static String getName(Context context, Uri docUri) {
        Cursor c = context.getContentResolver().query(docUri,
                new String[]{DocumentsContract.Document.COLUMN_DISPLAY_NAME},
                null, null, null);
        if (c != null) {
            try {
                if (c.moveToFirst()) {
                    return c.getString(0);
                }
            } finally {
                c.close();
            }
        }
        return "";
    }
}
