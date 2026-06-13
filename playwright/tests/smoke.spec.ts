import { test, expect } from '@playwright/test';

const qaLogin = process.env.PW_QA_LOGIN;
const qaPassword = process.env.PW_QA_PASSWORD;

test.describe('public smoke', () => {
  test('login page loads and renders admin form', async ({ page }, testInfo) => {
    await page.goto('/login');

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByRole('heading', { name: /admin login/i })).toBeVisible();
    await expect(page.getByLabel(/email or username/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();

    await page.screenshot({
      path: testInfo.outputPath('login-page.png'),
      fullPage: true,
    });
  });

  test('root URL responds and redirects to a usable entry page', async ({ page }, testInfo) => {
    await page.goto('/');

    await expect(page).toHaveURL(/\/(login|admin|staff\/dashboard)/);

    await page.screenshot({
      path: testInfo.outputPath('root-entry.png'),
      fullPage: true,
    });
  });
});

test.describe('authenticated admin smoke', () => {
  test.skip(!qaLogin || !qaPassword, 'Set PW_QA_LOGIN and PW_QA_PASSWORD to run authenticated UI smoke checks.');

  test('admin can log in and open key admin screens', async ({ page }, testInfo) => {
    await page.goto('/login');
    await page.getByLabel(/email or username/i).fill(qaLogin ?? '');
    await page.getByLabel(/^password$/i).fill(qaPassword ?? '');

    await Promise.all([
      page.waitForURL(/\/(admin|staff\/dashboard)/),
      page.getByRole('button', { name: /login/i }).click(),
    ]);

    await expect(page).toHaveURL(/\/admin/);

    await page.goto('/admin/settings');
    await expect(page.getByRole('heading', { name: /settings/i })).toBeVisible();
    await page.screenshot({
      path: testInfo.outputPath('settings-page.png'),
      fullPage: true,
    });

    await page.goto('/admin/feedback-center');
    await expect(page.locator('body')).toContainText('Cases');
    await page.screenshot({
      path: testInfo.outputPath('feedback-center.png'),
      fullPage: true,
    });
  });
});
