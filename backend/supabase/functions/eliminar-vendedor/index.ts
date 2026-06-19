// AUTOLAND — Edge Function: eliminar-vendedor
// Borra un vendedor de Auth (y por cascade su perfil). Solo admin.
//
// Deploy: supabase functions deploy eliminar-vendedor

import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const SUPABASE_URL = Deno.env.get("SUPABASE_URL")!;
const SERVICE_ROLE_KEY = Deno.env.get("SUPABASE_SERVICE_ROLE_KEY")!;

Deno.serve(async (req) => {
  const corsHeaders = {
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
  };

  if (req.method === "OPTIONS") {
    return new Response("ok", { headers: corsHeaders });
  }

  try {
    const authHeader = req.headers.get("Authorization") ?? "";
    const callerClient = createClient(SUPABASE_URL, SERVICE_ROLE_KEY, {
      global: { headers: { Authorization: authHeader } },
    });

    const { data: callerUser, error: callerErr } = await callerClient.auth.getUser();
    if (callerErr || !callerUser?.user) {
      return new Response(JSON.stringify({ error: "No autenticado." }), {
        status: 401,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const adminClient = createClient(SUPABASE_URL, SERVICE_ROLE_KEY);

    const { data: perfilCaller } = await adminClient
      .from("perfiles")
      .select("rol")
      .eq("id", callerUser.user.id)
      .single();

    if (perfilCaller?.rol !== "admin") {
      return new Response(JSON.stringify({ error: "Solo un admin puede eliminar vendedores." }), {
        status: 403,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const { idVendedor } = await req.json();
    if (!idVendedor) {
      return new Response(JSON.stringify({ error: "Falta idVendedor." }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    // No permitir auto-eliminación ni eliminar a otro admin por error
    const { data: target } = await adminClient
      .from("perfiles")
      .select("rol")
      .eq("id", idVendedor)
      .single();

    if (!target || target.rol !== "vendedor") {
      return new Response(JSON.stringify({ error: "Solo se pueden eliminar cuentas de vendedor." }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    const { error: delErr } = await adminClient.auth.admin.deleteUser(idVendedor);
    if (delErr) {
      return new Response(JSON.stringify({ error: delErr.message }), {
        status: 400,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    return new Response(JSON.stringify({ ok: true }), {
      status: 200,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });
  } catch (e) {
    return new Response(JSON.stringify({ error: String(e) }), {
      status: 500,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });
  }
});
