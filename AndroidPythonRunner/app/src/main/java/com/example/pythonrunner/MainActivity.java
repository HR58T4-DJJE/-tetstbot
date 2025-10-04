package com.example.pythonrunner;

import android.Manifest;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.provider.DocumentsContract;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultCallback;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;

import com.chaquo.python.PyObject;
import com.chaquo.python.Python;
import com.chaquo.python.android.AndroidPlatform;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

public class MainActivity extends AppCompatActivity {

    private TextView txtPath;
    private TextView txtOutput;
    private Uri pickedTreeUri = null;

    private final ActivityResultLauncher<String> requestPermissionLauncher =
            registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
                if (!isGranted) {
                    Toast.makeText(this, "Storage permission denied", Toast.LENGTH_SHORT).show();
                }
            });

    private final ActivityResultLauncher<Uri> openTreeLauncher =
            registerForActivityResult(new ActivityResultContracts.OpenDocumentTree(), new ActivityResultCallback<Uri>() {
                @Override
                public void onActivityResult(Uri uri) {
                    if (uri != null) {
                        pickedTreeUri = uri;
                        txtPath.setText(uri.toString());
                        getContentResolver().takePersistableUriPermission(uri,
                                Intent.FLAG_GRANT_READ_URI_PERMISSION | Intent.FLAG_GRANT_WRITE_URI_PERMISSION);
                    }
                }
            });

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        txtPath = findViewById(R.id.txtPath);
        txtOutput = findViewById(R.id.txtOutput);
        Button btnSelect = findViewById(R.id.btnSelect);
        Button btnRun = findViewById(R.id.btnRun);

        if (! Python.isStarted()) {
            Python.start(new AndroidPlatform(this));
        }

        if (Build.VERSION.SDK_INT < 33) {
            requestPermissionLauncher.launch(Manifest.permission.READ_EXTERNAL_STORAGE);
        }

        btnSelect.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                openTreeLauncher.launch(null);
            }
        });

        btnRun.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                runSelectedProject();
            }
        });
    }

    private void runSelectedProject() {
        if (pickedTreeUri == null) {
            Toast.makeText(this, "Select a project folder first", Toast.LENGTH_SHORT).show();
            return;
        }

        File appFiles = new File(getFilesDir(), "projects");
        if (!appFiles.exists()) appFiles.mkdirs();

        File projectDir = new File(appFiles, "selected");
        if (projectDir.exists()) deleteRecursively(projectDir);
        projectDir.mkdirs();

        copySelectedTreeToLocal(pickedTreeUri, projectDir);

        File mainPy = new File(projectDir, "main.py");
        if (!mainPy.exists()) {
            appendOutput("main.py not found in the selected folder\n");
            return;
        }

        try {
            Python py = Python.getInstance();
            PyObject runner = py.getModule("py_runner");
            PyObject result = runner.callAttr("run_script", mainPy.getAbsolutePath());
            appendOutput(result.toString() + "\n");
        } catch (Exception e) {
            appendOutput("Error: " + e.getMessage() + "\n");
        }
    }

    private void appendOutput(@NonNull String text) {
        runOnUiThread(() -> txtOutput.append(text));
    }

    private void copySelectedTreeToLocal(Uri treeUri, File destDir) {
        try {
            Uri rootDocUri = DocumentsContract.buildDocumentUriUsingTree(
                    treeUri, DocumentsContract.getTreeDocumentId(treeUri));
            copyDocumentDirectory(treeUri, rootDocUri, destDir);
        } catch (Exception e) {
            appendOutput("Copy error: " + e.getMessage() + "\n");
        }
    }

    private void copyDocumentDirectory(Uri treeUri, Uri parentDocUri, File destDir) {
        try {
            String parentId = DocumentsContract.getDocumentId(parentDocUri);
            for (Uri childDoc : AndroidDocuments.listChildren(this, treeUri, parentId)) {
                String name = AndroidDocuments.getName(this, childDoc);
                boolean isDir = AndroidDocuments.isDirectory(this, childDoc);
                if (isDir) {
                    File sub = new File(destDir, name);
                    sub.mkdirs();
                    copyDocumentDirectory(treeUri, childDoc, sub);
                } else {
                    copySingleFile(childDoc, new File(destDir, name));
                }
            }
        } catch (Exception e) {
            appendOutput("Copy error: " + e.getMessage() + "\n");
        }
    }

    private void copySingleFile(Uri uri, File dest) throws IOException {
        try (InputStream in = getContentResolver().openInputStream(uri);
             OutputStream out = new FileOutputStream(dest)) {
            byte[] buf = new byte[8192];
            int len;
            while ((len = in.read(buf)) > 0) {
                out.write(buf, 0, len);
            }
        }
    }

    private void deleteRecursively(File file) {
        if (file.isDirectory()) {
            File[] children = file.listFiles();
            if (children != null) {
                for (File child : children) {
                    deleteRecursively(child);
                }
            }
        }
        file.delete();
    }
}
