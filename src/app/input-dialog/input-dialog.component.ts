import { Component, Inject } from '@angular/core';
import { FormsModule, NgModel } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormField, MatLabel } from '@angular/material/form-field';

@Component({
  selector: 'app-input-dialog',
  standalone: true,
  imports: [MatFormField, FormsModule, MatLabel],
  templateUrl: './input-dialog.component.html',
  styleUrl: './input-dialog.component.css',
})
export class InputDialogComponent {
  constructor(
    public dialogRef: MatDialogRef<InputDialogComponent>,
    @Inject(MAT_DIALOG_DATA)
    public data: { task: { name: string; dueDate: string } }
  ) {}

  onCancel(): void {
    this.dialogRef.close();
  }

  onSave(): void {
    this.dialogRef.close(this.data.task); // Return the updated task with name and dueDate
  }
}
