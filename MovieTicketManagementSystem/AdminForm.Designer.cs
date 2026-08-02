namespace MovieTicketManagementSystem
{
    partial class AdminForm
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.panel1 = new System.Windows.Forms.Panel();
            this.pictureBox2 = new System.Windows.Forms.PictureBox();
            this.addMovie_btn = new System.Windows.Forms.Button();
            this.logout_btn = new System.Windows.Forms.Button();
            this.dashbord_btn = new System.Windows.Forms.Button();
            this.addStaff_btn = new System.Windows.Forms.Button();
            this.panel2 = new System.Windows.Forms.Panel();
            this.pictureBox1 = new System.Windows.Forms.PictureBox();
            this.label3 = new System.Windows.Forms.Label();
            this.label2 = new System.Windows.Forms.Label();
            this.btnAclose = new System.Windows.Forms.Label();
            this.panel3 = new System.Windows.Forms.Panel();
            this.addMovieForm1 = new MovieTicketManagementSystem.AddMovieForm();
            this.addStaffForm1 = new MovieTicketManagementSystem.AddStaffForm();
            this.dasboardForm1 = new MovieTicketManagementSystem.dasboardForm();
            this.panel1.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).BeginInit();
            this.panel2.SuspendLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            this.panel3.SuspendLayout();
            this.SuspendLayout();
            // 
            // panel1
            // 
            this.panel1.BackColor = System.Drawing.Color.Black;
            this.panel1.Controls.Add(this.pictureBox2);
            this.panel1.Controls.Add(this.addMovie_btn);
            this.panel1.Controls.Add(this.logout_btn);
            this.panel1.Controls.Add(this.dashbord_btn);
            this.panel1.Controls.Add(this.addStaff_btn);
            this.panel1.Dock = System.Windows.Forms.DockStyle.Left;
            this.panel1.Location = new System.Drawing.Point(0, 0);
            this.panel1.Name = "panel1";
            this.panel1.Size = new System.Drawing.Size(213, 750);
            this.panel1.TabIndex = 0;
            // 
            // pictureBox2
            // 
            this.pictureBox2.Image = global::MovieTicketManagementSystem.Properties.Resources.Movie_Ticket;
            this.pictureBox2.Location = new System.Drawing.Point(47, 45);
            this.pictureBox2.Name = "pictureBox2";
            this.pictureBox2.Size = new System.Drawing.Size(100, 100);
            this.pictureBox2.TabIndex = 0;
            this.pictureBox2.TabStop = false;
            // 
            // addMovie_btn
            // 
            this.addMovie_btn.BackColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.addMovie_btn.FlatAppearance.BorderSize = 0;
            this.addMovie_btn.FlatStyle = System.Windows.Forms.FlatStyle.Flat;
            this.addMovie_btn.Font = new System.Drawing.Font("Arial Narrow", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.addMovie_btn.ForeColor = System.Drawing.Color.Black;
            this.addMovie_btn.Image = global::MovieTicketManagementSystem.Properties.Resources.add_movie;
            this.addMovie_btn.ImageAlign = System.Drawing.ContentAlignment.MiddleLeft;
            this.addMovie_btn.Location = new System.Drawing.Point(19, 332);
            this.addMovie_btn.Name = "addMovie_btn";
            this.addMovie_btn.Size = new System.Drawing.Size(175, 45);
            this.addMovie_btn.TabIndex = 1;
            this.addMovie_btn.Text = "ADD MOVIE";
            this.addMovie_btn.UseVisualStyleBackColor = false;
            this.addMovie_btn.Click += new System.EventHandler(this.addMovie_btn_Click);
            // 
            // logout_btn
            // 
            this.logout_btn.BackColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.logout_btn.FlatAppearance.BorderSize = 0;
            this.logout_btn.FlatStyle = System.Windows.Forms.FlatStyle.Flat;
            this.logout_btn.Font = new System.Drawing.Font("Arial Narrow", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.logout_btn.ForeColor = System.Drawing.Color.Black;
            this.logout_btn.Image = global::MovieTicketManagementSystem.Properties.Resources.logout;
            this.logout_btn.ImageAlign = System.Drawing.ContentAlignment.MiddleLeft;
            this.logout_btn.Location = new System.Drawing.Point(19, 677);
            this.logout_btn.Name = "logout_btn";
            this.logout_btn.Size = new System.Drawing.Size(175, 45);
            this.logout_btn.TabIndex = 1;
            this.logout_btn.Text = "LOGOUT";
            this.logout_btn.UseVisualStyleBackColor = false;
            this.logout_btn.Click += new System.EventHandler(this.logout_btn_Click);
            // 
            // dashbord_btn
            // 
            this.dashbord_btn.BackColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.dashbord_btn.FlatAppearance.BorderSize = 0;
            this.dashbord_btn.FlatStyle = System.Windows.Forms.FlatStyle.Flat;
            this.dashbord_btn.Font = new System.Drawing.Font("Arial Narrow", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.dashbord_btn.ForeColor = System.Drawing.Color.Black;
            this.dashbord_btn.Image = global::MovieTicketManagementSystem.Properties.Resources.dashboard;
            this.dashbord_btn.ImageAlign = System.Drawing.ContentAlignment.MiddleLeft;
            this.dashbord_btn.Location = new System.Drawing.Point(19, 230);
            this.dashbord_btn.Name = "dashbord_btn";
            this.dashbord_btn.Size = new System.Drawing.Size(175, 45);
            this.dashbord_btn.TabIndex = 1;
            this.dashbord_btn.Text = "DASHBOARD";
            this.dashbord_btn.UseVisualStyleBackColor = false;
            this.dashbord_btn.Click += new System.EventHandler(this.dashbord_btn_Click);
            // 
            // addStaff_btn
            // 
            this.addStaff_btn.BackColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.addStaff_btn.FlatAppearance.BorderSize = 0;
            this.addStaff_btn.FlatStyle = System.Windows.Forms.FlatStyle.Flat;
            this.addStaff_btn.Font = new System.Drawing.Font("Arial Narrow", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.addStaff_btn.ForeColor = System.Drawing.Color.Black;
            this.addStaff_btn.Image = global::MovieTicketManagementSystem.Properties.Resources.add_staff;
            this.addStaff_btn.ImageAlign = System.Drawing.ContentAlignment.MiddleLeft;
            this.addStaff_btn.Location = new System.Drawing.Point(19, 281);
            this.addStaff_btn.Name = "addStaff_btn";
            this.addStaff_btn.Size = new System.Drawing.Size(175, 45);
            this.addStaff_btn.TabIndex = 1;
            this.addStaff_btn.Text = "ADD STAFF";
            this.addStaff_btn.UseVisualStyleBackColor = false;
            this.addStaff_btn.Click += new System.EventHandler(this.addStaff_btn_Click);
            // 
            // panel2
            // 
            this.panel2.BackColor = System.Drawing.Color.Black;
            this.panel2.Controls.Add(this.pictureBox1);
            this.panel2.Controls.Add(this.label3);
            this.panel2.Controls.Add(this.label2);
            this.panel2.Controls.Add(this.btnAclose);
            this.panel2.Dock = System.Windows.Forms.DockStyle.Top;
            this.panel2.Location = new System.Drawing.Point(213, 0);
            this.panel2.Name = "panel2";
            this.panel2.Size = new System.Drawing.Size(987, 44);
            this.panel2.TabIndex = 1;
            // 
            // pictureBox1
            // 
            this.pictureBox1.Image = global::MovieTicketManagementSystem.Properties.Resources.user;
            this.pictureBox1.Location = new System.Drawing.Point(762, 9);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(28, 28);
            this.pictureBox1.TabIndex = 0;
            this.pictureBox1.TabStop = false;
            // 
            // label3
            // 
            this.label3.AutoSize = true;
            this.label3.Font = new System.Drawing.Font("Arial Rounded MT Bold", 11F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label3.ForeColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.label3.Location = new System.Drawing.Point(796, 13);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(126, 17);
            this.label3.TabIndex = 2;
            this.label3.Text = "Welcome,Admin";
            // 
            // label2
            // 
            this.label2.AutoSize = true;
            this.label2.Font = new System.Drawing.Font("Arial Rounded MT Bold", 11F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.label2.ForeColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.label2.Location = new System.Drawing.Point(28, 13);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(182, 17);
            this.label2.TabIndex = 1;
            this.label2.Text = "ARCAHANA THEATEAR";
            // 
            // btnAclose
            // 
            this.btnAclose.AutoSize = true;
            this.btnAclose.Font = new System.Drawing.Font("Arial Rounded MT Bold", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.btnAclose.ForeColor = System.Drawing.Color.FromArgb(((int)(((byte)(247)))), ((int)(((byte)(199)))), ((int)(((byte)(37)))));
            this.btnAclose.Location = new System.Drawing.Point(965, 12);
            this.btnAclose.Name = "btnAclose";
            this.btnAclose.Size = new System.Drawing.Size(18, 18);
            this.btnAclose.TabIndex = 0;
            this.btnAclose.Text = "X";
            this.btnAclose.Click += new System.EventHandler(this.btnAclose_Click);
            // 
            // panel3
            // 
            this.panel3.Controls.Add(this.dasboardForm1);
            this.panel3.Controls.Add(this.addStaffForm1);
            this.panel3.Controls.Add(this.addMovieForm1);
            this.panel3.Dock = System.Windows.Forms.DockStyle.Fill;
            this.panel3.Location = new System.Drawing.Point(213, 44);
            this.panel3.Name = "panel3";
            this.panel3.Size = new System.Drawing.Size(987, 706);
            this.panel3.TabIndex = 2;
            // 
            // addMovieForm1
            // 
            this.addMovieForm1.Location = new System.Drawing.Point(0, 0);
            this.addMovieForm1.Name = "addMovieForm1";
            this.addMovieForm1.Size = new System.Drawing.Size(987, 706);
            this.addMovieForm1.TabIndex = 0;
            // 
            // addStaffForm1
            // 
            this.addStaffForm1.Location = new System.Drawing.Point(0, -3);
            this.addStaffForm1.Name = "addStaffForm1";
            this.addStaffForm1.Size = new System.Drawing.Size(987, 706);
            this.addStaffForm1.TabIndex = 1;
            // 
            // dasboardForm1
            // 
            this.dasboardForm1.Location = new System.Drawing.Point(0, 0);
            this.dasboardForm1.Name = "dasboardForm1";
            this.dasboardForm1.Size = new System.Drawing.Size(987, 706);
            this.dasboardForm1.TabIndex = 2;
            // 
            // AdminForm
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(1200, 750);
            this.Controls.Add(this.panel3);
            this.Controls.Add(this.panel2);
            this.Controls.Add(this.panel1);
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.None;
            this.Name = "AdminForm";
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "AdminForm";
            this.panel1.ResumeLayout(false);
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).EndInit();
            this.panel2.ResumeLayout(false);
            this.panel2.PerformLayout();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).EndInit();
            this.panel3.ResumeLayout(false);
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.Panel panel1;
        private System.Windows.Forms.Panel panel2;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.Label btnAclose;
        private System.Windows.Forms.Button addMovie_btn;
        private System.Windows.Forms.Button logout_btn;
        private System.Windows.Forms.Button dashbord_btn;
        private System.Windows.Forms.Button addStaff_btn;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.PictureBox pictureBox2;
        private System.Windows.Forms.PictureBox pictureBox1;
        private System.Windows.Forms.Panel panel3;
        private dasboardForm dasboardForm1;
        private AddStaffForm addStaffForm1;
        private AddMovieForm addMovieForm1;
    }
}